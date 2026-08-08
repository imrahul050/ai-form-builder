<div class="space-y-6">
    <!-- Top Action Bar -->
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 bg-white border border-slate-200 p-5 rounded-2xl shadow-sm">
        <div class="flex-1 space-y-2">
            <input type="text" wire:model.live="title" wire:change="syncRawJson" class="w-full text-2xl font-bold bg-transparent border-b border-transparent hover:border-slate-300 focus:border-indigo-600 focus:outline-none text-slate-900 px-1 py-0.5 transition" placeholder="Form Title...">
            <input type="text" wire:model.live="description" wire:change="syncRawJson" class="w-full text-sm text-slate-500 bg-transparent border-b border-transparent hover:border-slate-300 focus:border-indigo-600 focus:outline-none px-1 py-0.5 transition" placeholder="Form Description (optional)...">
        </div>

        <div class="flex items-center space-x-3">
            <!-- Mode Switcher -->
            <div class="bg-slate-100 p-1 rounded-xl border border-slate-200 flex items-center space-x-1">
                <button wire:click="switchTab('visual')" class="px-3.5 py-1.5 rounded-lg text-xs font-semibold transition {{ $activeTab === 'visual' ? 'bg-white text-indigo-600 shadow-sm' : 'text-slate-600 hover:text-slate-900' }}">
                    <i class="fa-solid fa-paintbrush mr-1.5"></i> Visual Canvas
                </button>
                <button wire:click="switchTab('json')" class="px-3.5 py-1.5 rounded-lg text-xs font-semibold transition {{ $activeTab === 'json' ? 'bg-white text-indigo-600 shadow-sm' : 'text-slate-600 hover:text-slate-900' }}">
                    <i class="fa-solid fa-code mr-1.5"></i> Raw JSON Editor
                </button>
            </div>

            <!-- Preview Saved Form Button -->
            @if ($form && $form->exists)
                <a href="{{ route('forms.public', $form->public_slug) }}" target="_blank" class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-semibold border border-slate-200 transition flex items-center space-x-2">
                    <i class="fa-solid fa-arrow-up-right-from-square text-indigo-600"></i>
                    <span>Preview Saved Form</span>
                </a>
            @endif

            <!-- Save Button with Loading State -->
            <button wire:click="saveForm" wire:loading.attr="disabled" wire:target="saveForm" class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 disabled:opacity-50 text-white text-sm font-semibold shadow-sm transition flex items-center space-x-2">
                <i wire:loading.remove wire:target="saveForm" class="fa-solid fa-floppy-disk"></i>
                <i wire:loading wire:target="saveForm" class="fa-solid fa-spinner fa-spin"></i>
                <span wire:loading.remove wire:target="saveForm">Save Form</span>
                <span wire:loading wire:target="saveForm">Saving...</span>
            </button>
        </div>
    </div>

    <!-- Validation Errors Banner -->
    @if (!empty($validationErrors))
        <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-sm space-y-1 shadow-sm">
            <div class="font-bold flex items-center space-x-2">
                <i class="fa-solid fa-triangle-exclamation text-rose-600"></i>
                <span>Schema Validation Error</span>
            </div>
            <ul class="list-disc list-inside space-y-0.5 text-xs text-rose-700 pl-2">
                @foreach ($validationErrors as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Main Workspace Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        @if ($activeTab === 'visual')
            <!-- Left Field Palette / Drag Source -->
            <div class="lg:col-span-3 bg-white border border-slate-200 p-5 rounded-2xl shadow-sm space-y-4 h-fit sticky top-24">
                <h3 class="text-xs font-bold text-slate-500 uppercase tracking-wider">Field Palette</h3>
                <div class="grid grid-cols-2 gap-2 text-xs">
                    @php
                        $fieldPalette = [
                            ['type' => 'text', 'label' => 'Text Input', 'icon' => 'fa-font'],
                            ['type' => 'textarea', 'label' => 'Text Area', 'icon' => 'fa-paragraph'],
                            ['type' => 'number', 'label' => 'Number', 'icon' => 'fa-hashtag'],
                            ['type' => 'email', 'label' => 'Email', 'icon' => 'fa-envelope'],
                            ['type' => 'phone', 'label' => 'Phone', 'icon' => 'fa-phone'],
                            ['type' => 'date', 'label' => 'Date', 'icon' => 'fa-calendar-days'],
                            ['type' => 'dropdown', 'label' => 'Dropdown', 'icon' => 'fa-square-caret-down'],
                            ['type' => 'radio', 'label' => 'Radio Buttons', 'icon' => 'fa-circle-dot'],
                            ['type' => 'checkbox', 'label' => 'Checkboxes', 'icon' => 'fa-square-check'],
                            ['type' => 'file', 'label' => 'File Upload', 'icon' => 'fa-cloud-arrow-up'],
                            ['type' => 'rating', 'label' => 'Star Rating', 'icon' => 'fa-star'],
                            ['type' => 'section_heading', 'label' => 'Heading', 'icon' => 'fa-heading'],
                        ];
                    @endphp

                    @foreach ($fieldPalette as $item)
                        <button wire:click="addField({{ $selectedSectionIndex ?? 0 }}, '{{ $item['type'] }}')" class="p-3 bg-slate-50 hover:bg-indigo-50 border border-slate-200 hover:border-indigo-300 rounded-xl text-left transition text-slate-700 hover:text-indigo-900 flex flex-col items-center justify-center text-center space-y-1.5 group">
                            <i class="fa-solid {{ $item['icon'] }} text-indigo-600 text-base group-hover:scale-110 transition"></i>
                            <span class="font-medium text-[11px]">{{ $item['label'] }}</span>
                        </button>
                    @endforeach
                </div>

                <div class="pt-4 border-t border-slate-200">
                    <button wire:click="addSection" class="w-full py-2.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 border border-indigo-200 rounded-xl text-xs font-semibold transition flex items-center justify-center space-x-2">
                        <i class="fa-solid fa-folder-plus"></i>
                        <span>Add New Section</span>
                    </button>
                </div>
            </div>

            <!-- Middle Visual Canvas -->
            <div class="lg:col-span-6 space-y-6">
                @if (empty($schema['sections']))
                    <div class="p-12 text-center bg-white border border-slate-200 rounded-2xl shadow-sm">
                        <p class="text-slate-500 text-sm mb-4">No sections created yet. Click "Add New Section" to start.</p>
                        <button wire:click="addSection" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-semibold shadow-sm transition">
                            + Add New Section
                        </button>
                    </div>
                @else
                    @foreach ($schema['sections'] as $sIndex => $section)
                        <div wire:key="section_{{ $sIndex }}_{{ $section['id'] ?? $sIndex }}" class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
                            <!-- Section Header with Delete Button -->
                            <div class="p-4 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
                                <div class="flex-1 pr-4">
                                    <input type="text" wire:model.live="schema.sections.{{ $sIndex }}.title" wire:change="syncRawJson" class="text-base font-bold bg-transparent text-slate-900 border-b border-transparent hover:border-slate-300 focus:border-indigo-600 focus:outline-none w-full px-1">
                                    <input type="text" wire:model.live="schema.sections.{{ $sIndex }}.description" wire:change="syncRawJson" class="text-xs text-slate-500 bg-transparent border-b border-transparent hover:border-slate-300 focus:border-indigo-600 focus:outline-none w-full px-1 mt-0.5" placeholder="Section subtext...">
                                </div>
                                <div class="flex items-center space-x-2">
                                    <span class="text-xs text-slate-400 font-mono">Sec {{ $sIndex + 1 }}</span>
                                    <button type="button"
                                        x-on:click="Swal.fire({
                                            title: 'Delete Section?',
                                            text: 'Are you sure you want to delete this section &quot;{{ addslashes($section['title']) }}&quot;?',
                                            icon: 'warning',
                                            showCancelButton: true,
                                            confirmButtonColor: '#ef4444',
                                            cancelButtonColor: '#64748b',
                                            confirmButtonText: 'Yes, delete it!'
                                        }).then((result) => {
                                            if (result.isConfirmed) {
                                                $wire.removeSection({{ $sIndex }});
                                            }
                                        })"
                                        class="px-2.5 py-1 text-slate-400 hover:text-rose-600 hover:bg-rose-50 border border-transparent hover:border-rose-200 rounded-lg text-xs font-medium transition flex items-center space-x-1"
                                        title="Delete Section">
                                        <i class="fa-solid fa-trash-can"></i>
                                        <span class="hidden sm:inline">Delete Section</span>
                                    </button>
                                </div>
                            </div>

                            <!-- Fields Container -->
                            <div class="p-4 space-y-3 min-h-[100px]">
                                @if (empty($section['fields']))
                                    <div class="p-6 border border-dashed border-slate-200 rounded-xl text-center bg-slate-50/50">
                                        <p class="text-xs text-slate-400">Click field palette items on the left to add fields to this section.</p>
                                    </div>
                                @else
                                    @foreach ($section['fields'] as $fIndex => $field)
                                        @php $isSelected = ($selectedSectionIndex === $sIndex && $selectedFieldIndex === $fIndex); @endphp
                                        <div wire:key="canvas_field_{{ $sIndex }}_{{ $fIndex }}_{{ $field['id'] ?? $fIndex }}" wire:click="selectField({{ $sIndex }}, {{ $fIndex }})" class="p-4 rounded-xl border transition cursor-pointer relative group {{ $isSelected ? 'bg-indigo-50/80 border-indigo-600 shadow-sm ring-2 ring-indigo-500/30' : 'bg-slate-50/50 border-slate-200 hover:border-slate-300' }}">
                                            <div class="flex items-center justify-between mb-2">
                                                <div class="flex items-center space-x-2">
                                                    <span class="font-bold text-sm {{ $isSelected ? 'text-indigo-900' : 'text-slate-900' }}">{{ $field['label'] }}</span>
                                                    @if (!empty($field['required']))
                                                        <span class="text-rose-500 text-xs font-bold">*</span>
                                                    @endif
                                                </div>
                                                <div class="flex items-center space-x-2 opacity-80 group-hover:opacity-100">
                                                    <span class="px-2 py-0.5 rounded bg-slate-200 text-[10px] text-slate-700 font-mono uppercase">{{ $field['type'] }}</span>
                                                    <button wire:click.stop="removeField({{ $sIndex }}, {{ $fIndex }})" class="text-slate-400 hover:text-rose-600 text-xs p-1 transition">
                                                        <i class="fa-solid fa-trash-can"></i>
                                                    </button>
                                                </div>
                                            </div>

                                            <div class="text-xs text-slate-600 bg-white p-2.5 rounded-lg border border-slate-200">
                                                @if (in_array($field['type'], ['text', 'email', 'phone', 'number', 'date']))
                                                    <span class="text-slate-400 italic">{{ $field['placeholder'] ?: 'Placeholder text...' }}</span>
                                                @elseif ($field['type'] === 'dropdown' || $field['type'] === 'radio' || $field['type'] === 'checkbox')
                                                    <div class="flex flex-wrap gap-1.5">
                                                        @foreach ($field['options'] ?? [] as $opt)
                                                            <span class="px-2 py-0.5 bg-slate-100 text-slate-700 rounded text-[11px] border border-slate-200">{{ $opt['label'] }}</span>
                                                        @endforeach
                                                    </div>
                                                @elseif ($field['type'] === 'file')
                                                    <span class="text-slate-500"><i class="fa-solid fa-cloud-arrow-up mr-1 text-indigo-600"></i> Upload Area</span>
                                                @else
                                                    <span class="text-slate-600">{{ $field['label'] }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                @endif
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>

            <!-- Right Inspector Sidebar -->
            <div class="lg:col-span-3 bg-white border border-slate-200 p-5 rounded-2xl shadow-sm space-y-4 h-fit sticky top-24">
                <div class="flex items-center justify-between border-b border-slate-100 pb-2">
                    <h3 class="text-xs font-bold text-slate-500 uppercase tracking-wider">Field Inspector</h3>
                    @if ($this->selectedField)
                        <span class="px-2 py-0.5 bg-indigo-50 text-indigo-700 font-mono text-[10px] rounded font-bold uppercase">
                            {{ $this->selectedField['type'] }}
                        </span>
                    @endif
                </div>

                @if ($selectedSectionIndex !== null && $selectedFieldIndex !== null && isset($schema['sections'][$selectedSectionIndex]['fields'][$selectedFieldIndex]))
                    @php $currField = $schema['sections'][$selectedSectionIndex]['fields'][$selectedFieldIndex]; @endphp
                    <div wire:key="field_inspector_{{ $selectedSectionIndex }}_{{ $selectedFieldIndex }}" class="space-y-4 text-xs">
                        <div>
                            <label class="block font-medium text-slate-700 mb-1">Field Label</label>
                            <input type="text" wire:model.live="schema.sections.{{ $selectedSectionIndex }}.fields.{{ $selectedFieldIndex }}.label" wire:change="syncRawJson" class="w-full bg-slate-50 border border-slate-200 rounded-lg p-2 text-slate-900 focus:border-indigo-600 focus:bg-white focus:outline-none font-medium">
                        </div>

                        <div>
                            <label class="block font-medium text-slate-700 mb-1">Field Key (Unique)</label>
                            <input type="text" wire:model.live="schema.sections.{{ $selectedSectionIndex }}.fields.{{ $selectedFieldIndex }}.key" wire:change="syncRawJson" class="w-full bg-slate-50 border border-slate-200 rounded-lg p-2 text-slate-900 font-mono focus:border-indigo-600 focus:bg-white focus:outline-none">
                        </div>

                        <div>
                            <label class="block font-medium text-slate-700 mb-1">Placeholder</label>
                            <input type="text" wire:model.live="schema.sections.{{ $selectedSectionIndex }}.fields.{{ $selectedFieldIndex }}.placeholder" wire:change="syncRawJson" class="w-full bg-slate-50 border border-slate-200 rounded-lg p-2 text-slate-900 focus:border-indigo-600 focus:bg-white focus:outline-none">
                        </div>

                        <div class="flex items-center space-x-2 pt-1">
                            <input type="checkbox" id="req_flag_{{ $selectedSectionIndex }}_{{ $selectedFieldIndex }}" wire:model.live="schema.sections.{{ $selectedSectionIndex }}.fields.{{ $selectedFieldIndex }}.required" wire:change="syncRawJson" class="rounded bg-slate-50 border-slate-300 text-indigo-600 focus:ring-0">
                            <label for="req_flag_{{ $selectedSectionIndex }}_{{ $selectedFieldIndex }}" class="font-medium text-slate-700">Required Field</label>
                        </div>

                        <!-- Dropdown / Choice Options Manager -->
                        @if (in_array($currField['type'], ['dropdown', 'radio', 'checkbox']))
                            <div wire:key="inspector_options_{{ $selectedSectionIndex }}_{{ $selectedFieldIndex }}" class="pt-3 border-t border-slate-200 space-y-3">
                                <div class="flex items-center justify-between">
                                    <span class="font-bold text-slate-900">Manage Options</span>
                                    <button wire:click="addOption" type="button" class="px-2.5 py-1 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-semibold rounded-lg text-[11px] border border-indigo-200 transition">
                                        + Add Option
                                    </button>
                                </div>

                                <div class="space-y-2 max-h-56 overflow-y-auto pr-1">
                                    @foreach ($currField['options'] ?? [] as $optIndex => $opt)
                                        <div wire:key="opt_item_{{ $selectedSectionIndex }}_{{ $selectedFieldIndex }}_{{ $optIndex }}" class="p-2.5 bg-slate-50 border border-slate-200 rounded-xl space-y-1.5 relative group">
                                            <div class="flex items-center justify-between">
                                                <span class="text-[10px] font-bold text-slate-400 uppercase">Option {{ $optIndex + 1 }}</span>
                                                <button wire:click="removeOption({{ $optIndex }})" type="button" class="text-slate-400 hover:text-rose-600 text-xs">
                                                    <i class="fa-solid fa-xmark"></i>
                                                </button>
                                            </div>

                                            <div class="grid grid-cols-2 gap-1.5">
                                                <div>
                                                    <label class="text-[10px] text-slate-500">Display Label</label>
                                                    <input type="text" wire:model.live="schema.sections.{{ $selectedSectionIndex }}.fields.{{ $selectedFieldIndex }}.options.{{ $optIndex }}.label" wire:change="syncRawJson" class="w-full bg-white border border-slate-200 rounded p-1.5 text-xs text-slate-900 focus:border-indigo-600 focus:outline-none">
                                                </div>
                                                <div>
                                                    <label class="text-[10px] text-slate-500">Option Value</label>
                                                    <input type="text" wire:model.live="schema.sections.{{ $selectedSectionIndex }}.fields.{{ $selectedFieldIndex }}.options.{{ $optIndex }}.value" wire:change="syncRawJson" class="w-full bg-white border border-slate-200 rounded p-1.5 text-xs text-slate-900 font-mono focus:border-indigo-600 focus:outline-none">
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                @else
                    <div class="p-6 text-center text-slate-400 text-xs">
                        <i class="fa-solid fa-arrow-pointer text-xl mb-2 text-slate-300"></i>
                        <p>Select any field on the canvas to inspect and edit its properties.</p>
                    </div>
                @endif
            </div>
        @else
            <!-- Raw JSON Editor View -->
            <div class="lg:col-span-12 bg-white border border-slate-200 p-6 rounded-2xl shadow-sm space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-base font-bold text-slate-900">Single Source of Truth JSON Schema</h3>
                        <p class="text-xs text-slate-500">Edits made here synchronize bi-directionally with the visual canvas.</p>
                    </div>
                </div>

                <textarea wire:model.live.debounce.500ms="rawJson" rows="22" class="w-full bg-slate-900 border border-slate-800 rounded-xl p-4 font-mono text-xs text-emerald-400 focus:border-indigo-600 focus:outline-none leading-relaxed"></textarea>
            </div>
        @endif
    </div>

    <!-- AI Prompt Generator Card / Component -->
    <div class="mt-8 border-t border-slate-200 pt-8">
        @livewire('ai-form-generator', ['form' => $form])
    </div>
</div>
