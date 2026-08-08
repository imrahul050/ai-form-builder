<?php

namespace App\Livewire;

use App\Models\Form;
use App\Models\FormVersion;
use App\Services\SchemaValidatorService;
use Livewire\Component;
use Illuminate\Support\Str;

class FormCanvas extends Component
{
    public ?Form $form = null;
    public string $title = 'Untitled Application Form';
    public string $description = 'Provide your information below.';
    public array $schema = [];
    public string $rawJson = '';
    public string $activeTab = 'visual'; // 'visual' or 'json'
    public ?int $selectedSectionIndex = 0;
    public ?int $selectedFieldIndex = 0;

    public array $validationErrors = [];
    public bool $isSaved = false;

    public function mount(?Form $form = null)
    {
        if ($form && $form->exists) {
            $this->form = $form;
            $this->title = $form->title;
            $this->description = $form->description ?? '';
            $this->schema = $form->schema;
            $this->isSaved = true;
        } else {
            $this->schema = [
                'title' => $this->title,
                'description' => $this->description,
                'settings' => [
                    'submit_label' => 'Submit Application',
                    'allow_csv_export' => true,
                ],
                'sections' => [
                    [
                        'id' => 'sec_1',
                        'title' => 'Personal Details',
                        'description' => 'Contact details & personal information',
                        'fields' => [
                            [
                                'id' => 'fld_1',
                                'key' => 'full_name',
                                'type' => 'text',
                                'label' => 'Full Name',
                                'placeholder' => 'John Doe',
                                'required' => true,
                            ],
                            [
                                'id' => 'fld_2',
                                'key' => 'email',
                                'type' => 'email',
                                'label' => 'Email Address',
                                'placeholder' => 'john@example.com',
                                'required' => true,
                            ],
                        ],
                    ]
                ],
            ];
        }

        $this->syncRawJson();
    }

    public function getSelectedFieldProperty()
    {
        if ($this->selectedSectionIndex !== null && $this->selectedFieldIndex !== null) {
            return $this->schema['sections'][$this->selectedSectionIndex]['fields'][$this->selectedFieldIndex] ?? null;
        }
        return null;
    }

    public function updatedSchema()
    {
        $this->syncRawJson();
    }

    public function syncRawJson()
    {
        $this->rawJson = json_encode($this->schema, JSON_PRETTY_PRINT);
    }

    public function updatedRawJson()
    {
        $decoded = json_decode($this->rawJson, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            $validator = new SchemaValidatorService();
            $check = $validator->validate($decoded);
            if ($check['is_valid']) {
                $this->schema = $decoded;
                $this->title = $decoded['title'] ?? $this->title;
                $this->validationErrors = [];
            } else {
                $this->validationErrors = $check['errors'];
            }
        } else {
            $this->validationErrors = ['Invalid JSON syntax format.'];
        }
    }

    public function switchTab(string $tab)
    {
        if ($tab === 'json') {
            $this->syncRawJson();
        }
        $this->activeTab = $tab;
    }

    public function selectField(int $sIndex, int $fIndex)
    {
        $this->selectedSectionIndex = $sIndex;
        $this->selectedFieldIndex = $fIndex;
    }

    public function addField(int $sectionIndex, string $type)
    {
        $count = rand(1000, 9999);
        $key = strtolower($type) . '_' . $count;
        $label = ucfirst($type) . ' Field';

        $field = [
            'id' => 'fld_' . Str::random(6),
            'key' => $key,
            'type' => $type,
            'label' => $label,
            'placeholder' => 'Enter ' . strtolower($label),
            'required' => false,
        ];

        if (in_array($type, ['dropdown', 'radio', 'checkbox'])) {
            $field['options'] = [
                ['label' => 'Option 1', 'value' => $key . '_opt_1'],
                ['label' => 'Option 2', 'value' => $key . '_opt_2'],
            ];
        }

        if (!isset($this->schema['sections'][$sectionIndex]['fields'])) {
            $this->schema['sections'][$sectionIndex]['fields'] = [];
        }

        $this->schema['sections'][$sectionIndex]['fields'][] = $field;
        $newFieldIndex = count($this->schema['sections'][$sectionIndex]['fields']) - 1;

        $this->selectField($sectionIndex, $newFieldIndex);
        $this->syncRawJson();
    }

    public function removeField(int $sIndex, int $fIndex)
    {
        array_splice($this->schema['sections'][$sIndex]['fields'], $fIndex, 1);

        if ($this->selectedSectionIndex === $sIndex && $this->selectedFieldIndex === $fIndex) {
            $this->selectedSectionIndex = null;
            $this->selectedFieldIndex = null;
        }

        $this->syncRawJson();
    }

    public function addOption()
    {
        if ($this->selectedSectionIndex === null || $this->selectedFieldIndex === null) return;
        if (!isset($this->schema['sections'][$this->selectedSectionIndex]['fields'][$this->selectedFieldIndex]['options'])) {
            $this->schema['sections'][$this->selectedSectionIndex]['fields'][$this->selectedFieldIndex]['options'] = [];
        }
        $currKey = $this->schema['sections'][$this->selectedSectionIndex]['fields'][$this->selectedFieldIndex]['key'] ?? 'field';
        $count = count($this->schema['sections'][$this->selectedSectionIndex]['fields'][$this->selectedFieldIndex]['options']) + 1;
        $this->schema['sections'][$this->selectedSectionIndex]['fields'][$this->selectedFieldIndex]['options'][] = [
            'label' => 'Option ' . $count,
            'value' => $currKey . '_opt_' . $count,
        ];
        $this->syncRawJson();
    }

    public function removeOption(int $optIndex)
    {
        if ($this->selectedSectionIndex === null || $this->selectedFieldIndex === null) return;
        if (isset($this->schema['sections'][$this->selectedSectionIndex]['fields'][$this->selectedFieldIndex]['options'][$optIndex])) {
            array_splice($this->schema['sections'][$this->selectedSectionIndex]['fields'][$this->selectedFieldIndex]['options'], $optIndex, 1);
            $this->syncRawJson();
        }
    }

    public function addSection()
    {
        $secCount = count($this->schema['sections']) + 1;
        $this->schema['sections'][] = [
            'id' => 'sec_' . Str::random(6),
            'title' => 'Section ' . $secCount,
            'description' => 'New section description',
            'fields' => [],
        ];
        $this->syncRawJson();
    }

    public function removeSection(int $sIndex)
    {
        if (isset($this->schema['sections'][$sIndex])) {
            array_splice($this->schema['sections'], $sIndex, 1);

            if ($this->selectedSectionIndex === $sIndex) {
                $this->selectedSectionIndex = null;
                $this->selectedFieldIndex = null;
            } elseif ($this->selectedSectionIndex > $sIndex) {
                $this->selectedSectionIndex--;
            }

            $this->syncRawJson();
            $this->dispatch('swal:toast', ['icon' => 'success', 'title' => 'Section deleted successfully!']);
        }
    }

    public function saveForm()
    {
        $validator = new SchemaValidatorService();
        $check = $validator->validate($this->schema);

        if (!$check['is_valid']) {
            $this->validationErrors = $check['errors'];
            $this->dispatch('swal:toast', ['icon' => 'error', 'title' => 'Form validation failed. Please check errors.']);
            return;
        }

        $this->schema['title'] = $this->title;

        if ($this->form && $this->form->exists) {
            $newVersion = $this->form->current_version + 1;
            $this->form->update([
                'title' => $this->title,
                'description' => $this->description,
                'schema' => $this->schema,
                'current_version' => $newVersion,
            ]);

            FormVersion::create([
                'form_id' => $this->form->id,
                'version_number' => $newVersion,
                'schema' => $this->schema,
                'change_summary' => 'Updated via Form Builder Canvas',
            ]);

            $msg = 'Form updated successfully! (v' . $newVersion . ')';
        } else {
            $this->form = Form::create([
                'tenant_id' => session('tenant_id', 1),
                'title' => $this->title,
                'description' => $this->description,
                'public_slug' => Str::slug($this->title) . '-' . Str::random(6),
                'schema' => $this->schema,
                'current_version' => 1,
            ]);

            FormVersion::create([
                'form_id' => $this->form->id,
                'version_number' => 1,
                'schema' => $this->schema,
                'change_summary' => 'Initial Form Creation',
            ]);

            $msg = 'Form created and saved successfully!';
        }

        $this->isSaved = true;
        session()->flash('status', $msg);
        $this->dispatch('swal:toast', ['icon' => 'success', 'title' => $msg]);
    }

    public function render()
    {
        return view('livewire.form-canvas');
    }
}
