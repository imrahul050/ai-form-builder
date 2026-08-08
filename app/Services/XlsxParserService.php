<?php

namespace App\Services;

use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Str;

class XlsxParserService
{
    /**
     * Parses an Excel spreadsheet (.xlsx) into a Form Builder schema structure.
     */
    public function parse(string $filePath): array
    {
        $spreadsheet = IOFactory::load($filePath);
        $worksheet = $spreadsheet->getActiveSheet();
        $rows = $worksheet->toArray();

        if (empty($rows)) {
            return [
                'title' => 'Empty Spreadsheet Form',
                'sections' => [],
            ];
        }

        $headers = array_shift($rows);
        $fields = [];
        $fieldCount = 1;

        foreach ($headers as $index => $header) {
            $rawLabel = trim((string)$header);
            if (empty($rawLabel)) continue;

            // Detect if required flag is present
            $isRequired = str_contains(strtolower($rawLabel), 'required') || str_contains($rawLabel, '*');

            // Strip trailing '*' or '(required)' from label text to prevent duplicate '*' rendering
            $cleanLabel = preg_replace('/\s*[\*\(]\s*required\s*[\)]?/i', '', $rawLabel);
            $cleanLabel = trim(rtrim($cleanLabel, '* '));

            if (empty($cleanLabel)) {
                $cleanLabel = 'Field ' . ($index + 1);
            }

            // Sample second row for data type detection
            $sampleValue = isset($rows[0][$index]) ? trim((string)$rows[0][$index]) : '';
            $fieldType = 'text';

            if (filter_var($sampleValue, FILTER_VALIDATE_EMAIL) || str_contains(strtolower($cleanLabel), 'email')) {
                $fieldType = 'email';
            } elseif (is_numeric($sampleValue) || str_contains(strtolower($cleanLabel), 'age') || str_contains(strtolower($cleanLabel), 'count')) {
                $fieldType = 'number';
            } elseif (strtotime($sampleValue) !== false || str_contains(strtolower($cleanLabel), 'date')) {
                $fieldType = 'date';
            } elseif (str_contains(strtolower($cleanLabel), 'phone')) {
                $fieldType = 'phone';
            }

            $key = Str::slug($cleanLabel, '_');
            if (strlen($key) > 40) {
                $key = substr($key, 0, 40);
            }
            if (empty($key)) {
                $key = 'col_' . ($index + 1);
            }

            $fields[] = [
                'id' => 'fld_' . $fieldCount,
                'key' => $key,
                'type' => $fieldType,
                'label' => $cleanLabel,
                'placeholder' => 'Enter ' . strtolower($cleanLabel),
                'required' => $isRequired,
            ];

            $fieldCount++;
        }

        return [
            'title' => 'Imported Excel Form (' . basename($filePath) . ')',
            'description' => 'Form schema generated from spreadsheet headers',
            'sections' => [
                [
                    'id' => 'sec_excel',
                    'title' => 'Spreadsheet Columns',
                    'description' => 'Mapped fields from Excel header row',
                    'fields' => $fields,
                ]
            ],
        ];
    }
}
