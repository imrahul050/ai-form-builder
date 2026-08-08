<?php

namespace App\Services;

use PhpOffice\PhpWord\IOFactory;
use Illuminate\Support\Str;

class DocxParserService
{
    /**
     * Parses a Word (.docx) file into a Form Builder schema structure.
     */
    public function parse(string $filePath): array
    {
        $phpWord = IOFactory::load($filePath);
        $sections = [];

        $currentSection = [
            'id' => 'sec_1',
            'title' => 'Imported Document Section',
            'description' => 'Automatically extracted from Word document',
            'fields' => [],
        ];

        $secCount = 1;
        $fieldCount = 1;

        foreach ($phpWord->getSections() as $docSection) {
            foreach ($docSection->getElements() as $element) {
                $class = get_class($element);

                if (str_contains($class, 'TextRun') || str_contains($class, 'Paragraph')) {
                    $text = '';
                    if (method_exists($element, 'getText')) {
                        $text = trim($element->getText());
                    } elseif (method_exists($element, 'getElements')) {
                        foreach ($element->getElements() as $child) {
                            if (method_exists($child, 'getText')) {
                                $text .= ' ' . $child->getText();
                            }
                        }
                        $text = trim($text);
                    }

                    if (empty($text)) continue;

                    // Check for Heading style -> Section
                    if (method_exists($element, 'getStyle') && method_exists($element->getStyle(), 'getStyleByName')) {
                        $style = $element->getStyle()->getStyleByName();
                        if ($style && (str_contains(strtolower($style), 'heading 1') || str_contains(strtolower($style), 'heading 2'))) {
                            if (!empty($currentSection['fields'])) {
                                $sections[] = $currentSection;
                            }
                            $secCount++;
                            $currentSection = [
                                'id' => 'sec_' . $secCount,
                                'title' => $text,
                                'description' => '',
                                'fields' => [],
                            ];
                            continue;
                        }
                    }

                    $rawText = $text;
                    $isRequired = str_contains(strtolower($rawText), 'required') || str_contains($rawText, '*');

                    // Strip trailing '*' or '(required)' from label text to prevent duplicate '*' rendering
                    $cleanLabel = preg_replace('/\s*[\*\(]\s*required\s*[\)]?/i', '', $rawText);
                    $cleanLabel = trim(rtrim($cleanLabel, '* '));

                    if (empty($cleanLabel)) {
                        $cleanLabel = 'Field ' . $fieldCount;
                    }

                    $fieldType = 'text';
                    $lowerLabel = strtolower($cleanLabel);

                    if (str_contains($lowerLabel, 'email')) {
                        $fieldType = 'email';
                    } elseif (str_contains($lowerLabel, 'phone') || str_contains($lowerLabel, 'mobile')) {
                        $fieldType = 'phone';
                    } elseif (str_contains($lowerLabel, 'date') || str_contains($lowerLabel, 'dob')) {
                        $fieldType = 'date';
                    } elseif (str_contains($lowerLabel, 'resume') || str_contains($lowerLabel, 'file') || str_contains($lowerLabel, 'upload')) {
                        $fieldType = 'file';
                    } elseif (str_contains($lowerLabel, 'description') || str_contains($lowerLabel, 'address') || str_contains($lowerLabel, 'bio')) {
                        $fieldType = 'textarea';
                    }

                    $key = Str::slug($cleanLabel, '_');
                    if (strlen($key) > 40) {
                        $key = substr($key, 0, 40);
                    }
                    if (empty($key)) {
                        $key = 'field_' . $fieldCount;
                    }

                    $currentSection['fields'][] = [
                        'id' => 'fld_' . $fieldCount,
                        'key' => $key,
                        'type' => $fieldType,
                        'label' => $cleanLabel,
                        'placeholder' => 'Enter ' . strtolower($cleanLabel),
                        'required' => $isRequired,
                    ];

                    $fieldCount++;
                }
            }
        }

        if (!empty($currentSection['fields'])) {
            $sections[] = $currentSection;
        }

        if (empty($sections)) {
            $sections[] = [
                'id' => 'sec_default',
                'title' => 'Imported Document Form',
                'description' => 'Extracted questions',
                'fields' => [
                    [
                        'id' => 'fld_1',
                        'key' => 'question_1',
                        'type' => 'text',
                        'label' => 'Sample Question 1',
                        'required' => true,
                    ]
                ]
            ];
        }

        return [
            'title' => 'Imported Word Form (' . basename($filePath) . ')',
            'description' => 'Generated from .docx document import',
            'sections' => $sections,
        ];
    }
}
