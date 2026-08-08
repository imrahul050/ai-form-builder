<?php

namespace Database\Seeders;

use App\Models\Form;
use App\Models\FormSubmission;
use App\Models\FormVersion;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create Default Tenant
        $tenant = Tenant::create([
            'uuid' => (string) Str::uuid(),
            'name' => 'Edunet Foundation Tech',
            'slug' => 'edunet-tech',
        ]);

        // 2. Create Demo Admin User
        $user = User::create([
            'tenant_id' => $tenant->id,
            'name' => 'Demo Lead Developer',
            'email' => 'admin@edunet.org',
            'password' => bcrypt('password123'),
            'role' => 'admin',
        ]);

        // 3. Create Sample AI-Generated Registration Form
        $sampleSchema = [
            'title' => 'Internship Application 2026',
            'description' => 'Complete this form to apply for our AI & Full-Stack Development Internship.',
            'settings' => [
                'submit_label' => 'Submit Application',
                'allow_csv_export' => true,
            ],
            'sections' => [
                [
                    'id' => 'sec_personal',
                    'title' => 'Personal & Contact Details',
                    'description' => 'Please provide accurate contact details.',
                    'fields' => [
                        [
                            'id' => 'fld_name',
                            'key' => 'applicant_name',
                            'type' => 'text',
                            'label' => 'Full Name',
                            'placeholder' => 'Jane Doe',
                            'required' => true,
                            'validation' => ['min' => 2, 'max' => 100],
                        ],
                        [
                            'id' => 'fld_email',
                            'key' => 'applicant_email',
                            'type' => 'email',
                            'label' => 'Email Address',
                            'placeholder' => 'jane@example.com',
                            'required' => true,
                        ],
                        [
                            'id' => 'fld_phone',
                            'key' => 'phone_number',
                            'type' => 'phone',
                            'label' => 'Phone Number',
                            'placeholder' => '+1 (555) 000-1234',
                            'required' => true,
                        ],
                    ],
                ],
                [
                    'id' => 'sec_background',
                    'title' => 'Skills & Resume Upload',
                    'description' => 'Tell us about your experience.',
                    'fields' => [
                        [
                            'id' => 'fld_primary_skill',
                            'key' => 'primary_skill',
                            'type' => 'dropdown',
                            'label' => 'Primary Technical Skill',
                            'required' => true,
                            'options' => [
                                ['label' => 'PHP / Laravel', 'value' => 'laravel'],
                                ['label' => 'Python / FastAPI', 'value' => 'fastapi'],
                                ['label' => 'React / Livewire', 'value' => 'react'],
                                ['label' => 'Database Architecture', 'value' => 'mysql'],
                            ],
                        ],
                        [
                            'id' => 'fld_bio',
                            'key' => 'cover_letter',
                            'type' => 'textarea',
                            'label' => 'Why do you want to join?',
                            'placeholder' => 'Briefly describe your projects and motivation...',
                            'required' => false,
                        ],
                        [
                            'id' => 'fld_resume',
                            'key' => 'resume_file',
                            'type' => 'file',
                            'label' => 'Upload Resume / Portfolio',
                            'required' => true,
                            'validation' => [
                                'allowed_types' => ['pdf', 'docx'],
                                'max_file_size_kb' => 5120,
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $form = Form::create([
            'tenant_id' => $tenant->id,
            'title' => 'Internship Application 2026',
            'description' => 'Complete this form to apply for our AI & Full-Stack Development Internship.',
            'public_slug' => 'internship-app-2026',
            'is_active' => true,
            'current_version' => 1,
            'schema' => $sampleSchema,
            'created_by' => $user->id,
        ]);

        // 4. Create Initial Version Snapshot
        FormVersion::create([
            'form_id' => $form->id,
            'version_number' => 1,
            'schema' => $sampleSchema,
            'change_summary' => 'Initial Form Published',
            'created_by' => $user->id,
        ]);

        // 5. Seed Sample Submissions
        FormSubmission::create([
            'tenant_id' => $tenant->id,
            'form_id' => $form->id,
            'form_version' => 1,
            'payload' => [
                'applicant_name' => 'Alex Rivera',
                'applicant_email' => 'alex.rivera@example.com',
                'phone_number' => '+1 (555) 234-5678',
                'primary_skill' => 'laravel',
                'cover_letter' => 'Passionate backend developer with 3 years of Laravel experience.',
            ],
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7)',
            'submitted_at' => now()->subHours(5),
        ]);

        FormSubmission::create([
            'tenant_id' => $tenant->id,
            'form_id' => $form->id,
            'form_version' => 1,
            'payload' => [
                'applicant_name' => 'Sophia Chen',
                'applicant_email' => 'sophia.chen@example.com',
                'phone_number' => '+1 (555) 876-5432',
                'primary_skill' => 'fastapi',
                'cover_letter' => 'AI engineer specializing in LLM prompt pipelines and REST APIs.',
            ],
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7)',
            'submitted_at' => now()->subHours(2),
        ]);
    }
}
