<?php

namespace App\Services;

class SchemaValidatorService
{
    /**
     * Validates if a raw array or JSON string conforms to the Form Builder schema specification.
     */
    public function validate(array $schema): array
    {
        $errors = [];

        if (empty($schema['title'])) {
            $errors[] = "Form title is required in schema root.";
        }

        if (!isset($schema['sections']) || !is_array($schema['sections'])) {
            $errors[] = "Schema must contain a 'sections' array.";
            return ['is_valid' => false, 'errors' => $errors];
        }

        $usedKeys = [];

        foreach ($schema['sections'] as $sIndex => $section) {
            if (empty($section['title'])) {
                $errors[] = "Section at index {$sIndex} is missing a title.";
            }

            if (!isset($section['fields']) || !is_array($section['fields'])) {
                $errors[] = "Section '{$section['title']}' must contain a 'fields' array.";
                continue;
            }

            foreach ($section['fields'] as $fIndex => $field) {
                if (empty($field['key'])) {
                    $errors[] = "Field at section {$sIndex}, index {$fIndex} is missing a unique key.";
                } else {
                    if (in_array($field['key'], $usedKeys)) {
                        $errors[] = "Duplicate field key '{$field['key']}' detected.";
                    }
                    $usedKeys[] = $field['key'];
                }

                if (empty($field['type'])) {
                    $errors[] = "Field '{$field['key']}' is missing a 'type'.";
                }

                if (empty($field['label'])) {
                    $errors[] = "Field '{$field['key']}' is missing a 'label'.";
                }
            }
        }

        return [
            'is_valid' => count($errors) === 0,
            'errors' => $errors,
        ];
    }
}
