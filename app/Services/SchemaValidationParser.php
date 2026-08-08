<?php

namespace App\Services;

class SchemaValidationParser
{
    /**
     * Converts a Form Builder JSON Schema into Laravel Validator rules.
     */
    public function parseRules(array $schema): array
    {
        $rules = [];

        if (empty($schema['sections'])) {
            return $rules;
        }

        foreach ($schema['sections'] as $section) {
            if (empty($section['fields'])) {
                continue;
            }

            foreach ($section['fields'] as $field) {
                $key = $field['key'] ?? null;
                if (!$key) continue;

                $fieldRules = [];

                // 1. Required flag
                if (!empty($field['required'])) {
                    $fieldRules[] = 'required';
                } else {
                    $fieldRules[] = 'nullable';
                }

                // 2. Type based rules
                switch ($field['type'] ?? 'text') {
                    case 'number':
                        $fieldRules[] = 'numeric';
                        break;
                    case 'email':
                        $fieldRules[] = 'email';
                        break;
                    case 'date':
                        $fieldRules[] = 'date';
                        break;
                    case 'file':
                        $fieldRules[] = 'file';
                        break;
                    case 'checkbox':
                        $fieldRules[] = 'array';
                        break;
                }

                // 3. Custom validation object constraints
                if (!empty($field['validation']) && is_array($field['validation'])) {
                    $v = $field['validation'];

                    if (isset($v['min']) && is_numeric($v['min'])) {
                        $fieldRules[] = 'min:' . $v['min'];
                    }
                    if (isset($v['max']) && is_numeric($v['max'])) {
                        $fieldRules[] = 'max:' . $v['max'];
                    }
                    if (!empty($v['regex'])) {
                        $fieldRules[] = 'regex:' . $v['regex'];
                    }
                    if (!empty($v['allowed_types']) && is_array($v['allowed_types'])) {
                        $fieldRules[] = 'mimes:' . implode(',', $v['allowed_types']);
                    }
                    if (isset($v['max_file_size_kb'])) {
                        $fieldRules[] = 'max:' . $v['max_file_size_kb'];
                    }
                }

                $rules[$key] = implode('|', $fieldRules);
            }
        }

        return $rules;
    }
}
