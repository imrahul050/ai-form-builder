<?php

namespace Tests\Unit;

use App\Services\SchemaValidationParser;
use App\Services\SchemaValidatorService;
use PHPUnit\Framework\TestCase;

class FormTest extends TestCase
{
    public function test_schema_validator_validates_correct_schema(): void
    {
        $validator = new SchemaValidatorService();
        $schema = [
            'title' => 'Test Form',
            'sections' => [
                [
                    'id' => 'sec_1',
                    'title' => 'Section 1',
                    'fields' => [
                        [
                            'id' => 'fld_1',
                            'key' => 'name',
                            'type' => 'text',
                            'label' => 'Full Name',
                        ]
                    ]
                ]
            ]
        ];

        $result = $validator->validate($schema);
        $this->assertTrue($result['is_valid']);
        $this->assertEmpty($result['errors']);
    }

    public function test_schema_validator_detects_duplicate_keys(): void
    {
        $validator = new SchemaValidatorService();
        $schema = [
            'title' => 'Invalid Form',
            'sections' => [
                [
                    'id' => 'sec_1',
                    'title' => 'Section 1',
                    'fields' => [
                        ['id' => 'fld_1', 'key' => 'email', 'type' => 'email', 'label' => 'Email 1'],
                        ['id' => 'fld_2', 'key' => 'email', 'type' => 'email', 'label' => 'Email 2'],
                    ]
                ]
            ]
        ];

        $result = $validator->validate($schema);
        $this->assertFalse($result['is_valid']);
        $this->assertStringContainsString('Duplicate field key', $result['errors'][0]);
    }

    public function test_schema_validation_parser_converts_rules(): void
    {
        $parser = new SchemaValidationParser();
        $schema = [
            'sections' => [
                [
                    'fields' => [
                        [
                            'key' => 'user_email',
                            'type' => 'email',
                            'required' => true,
                            'validation' => ['min' => 5]
                        ]
                    ]
                ]
            ]
        ];

        $rules = $parser->parseRules($schema);
        $this->assertArrayHasKey('user_email', $rules);
        $this->assertEquals('required|email|min:5', $rules['user_email']);
    }
}
