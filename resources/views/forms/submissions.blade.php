<x-app-layout>
    <x-slot name="title">Submissions: {{ $form->title }}</x-slot>

    <div class="space-y-6">
        <!-- Top Action Bar -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white border border-slate-200 p-6 rounded-2xl shadow-sm">
            <div>
                <h1 class="text-2xl font-bold text-slate-900">Form Submissions</h1>
                <p class="text-slate-500 text-sm mt-1">Form: <span class="text-indigo-600 font-semibold">{{ $form->title }}</span> ({{ $submissions->total() }} total entries)</p>
            </div>
            <div class="flex items-center space-x-3">
                <a href="{{ route('forms.index') }}" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-semibold rounded-xl transition">
                    <i class="fa-solid fa-arrow-left mr-1.5"></i> Back to Forms
                </a>
                <a href="{{ route('forms.export.csv', $form->id) }}" class="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-xl shadow-sm transition flex items-center space-x-2">
                    <i class="fa-solid fa-file-csv"></i>
                    <span>Export CSV</span>
                </a>
            </div>
        </div>

        <!-- Submissions Table Card -->
        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
            <div class="p-4 border-b border-slate-200">
                <form action="{{ route('forms.submissions', $form->id) }}" method="GET" class="flex gap-3">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search submission payload data..." class="flex-1 bg-slate-50 border border-slate-200 rounded-xl px-4 py-2 text-sm text-slate-900 focus:outline-none focus:border-indigo-600 focus:bg-white">
                    <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-xl transition shadow-sm">
                        <i class="fa-solid fa-magnifying-glass mr-1"></i> Search
                    </button>
                </form>
            </div>

            @if ($submissions->isEmpty())
                <div class="p-12 text-center text-slate-500">
                    <i class="fa-solid fa-inbox text-4xl mb-3 text-slate-300"></i>
                    <p class="font-medium text-slate-700">No submissions recorded yet.</p>
                    <p class="text-xs text-slate-400 mt-1">Submit responses using the public form link to see them listed here.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-slate-700">
                        <thead class="bg-slate-50 text-slate-500 uppercase text-xs font-semibold tracking-wider border-b border-slate-200">
                            <tr>
                                <th class="px-6 py-4">Submission ID</th>
                                <th class="px-6 py-4">Submitted At</th>
                                <th class="px-6 py-4">Submitted Data Summary</th>
                                <th class="px-6 py-4 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200">
                            @php
                                // Map field keys to labels from form schema
                                $fieldMap = [];
                                foreach ($form->schema['sections'] ?? [] as $sec) {
                                    foreach ($sec['fields'] ?? [] as $f) {
                                        if (isset($f['key'])) {
                                            $fieldMap[$f['key']] = $f['label'] ?? $f['key'];
                                        }
                                    }
                                }
                            @endphp

                            @foreach ($submissions as $sub)
                                @php $payload = $sub->payload ?? []; @endphp
                                <tr x-data="{ showModal: false }" class="hover:bg-slate-50/80 transition">
                                    <td class="px-6 py-4 font-mono text-xs text-indigo-600 font-bold">
                                        {{ substr($sub->submission_uuid, 0, 13) }}...
                                    </td>
                                    <td class="px-6 py-4 text-xs text-slate-500 whitespace-nowrap">
                                        <i class="fa-regular fa-clock mr-1 text-slate-400"></i>
                                        {{ $sub->submitted_at ? $sub->submitted_at->diffForHumans() : 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 text-xs text-slate-600">
                                        <div class="flex flex-wrap gap-2 max-w-lg">
                                            @php $count = 0; @endphp
                                            @foreach ($payload as $k => $val)
                                                @if ($count < 3)
                                                    <span class="px-2.5 py-1 bg-slate-100 border border-slate-200 rounded-lg text-[11px] text-slate-700">
                                                        <strong class="text-slate-900">{{ $fieldMap[$k] ?? ucfirst(str_replace('_', ' ', $k)) }}:</strong>
                                                        @if (is_array($val) && isset($val['is_file']))
                                                            <span class="text-indigo-600 font-semibold"><i class="fa-solid fa-file mr-1"></i> {{ $val['original_name'] }}</span>
                                                        @elseif (is_array($val))
                                                            {{ implode(', ', $val) }}
                                                        @else
                                                            {{ Str::limit((string)$val, 25) }}
                                                        @endif
                                                    </span>
                                                    @php $count++; @endphp
                                                @endif
                                            @endforeach
                                            @if (count($payload) > 3)
                                                <span class="px-2 py-1 bg-indigo-50 text-indigo-700 font-semibold rounded-lg text-[10px]">+{{ count($payload) - 3 }} more</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <button @click="showModal = true" type="button" class="px-3.5 py-1.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-semibold text-xs rounded-xl border border-indigo-200 shadow-sm transition inline-flex items-center space-x-1.5">
                                            <i class="fa-solid fa-eye"></i>
                                            <span>View Details</span>
                                        </button>

                                        <!-- User Friendly Submission Details Modal -->
                                        <div x-show="showModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                                            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                                                <!-- Backdrop -->
                                                <div x-show="showModal" x-transition.opacity @click="showModal = false" class="fixed inset-0 transition-opacity bg-slate-900/60 backdrop-blur-sm"></div>

                                                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                                                <!-- Modal Card Container -->
                                                <div x-show="showModal" x-transition class="inline-block w-full max-w-2xl my-8 overflow-hidden text-left align-middle transition-all transform bg-white border border-slate-200 rounded-3xl shadow-2xl">
                                                    <!-- Modal Header -->
                                                    <div class="px-6 py-5 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
                                                        <div>
                                                            <div class="flex items-center space-x-2">
                                                                <h3 class="text-lg font-bold text-slate-900">Submission Details</h3>
                                                                <span class="px-2.5 py-0.5 rounded bg-indigo-100 text-indigo-700 text-xs font-mono font-bold">v{{ $sub->form_version }}</span>
                                                            </div>
                                                            <p class="text-xs text-slate-500 font-mono mt-0.5">UUID: {{ $sub->submission_uuid }}</p>
                                                        </div>
                                                        <button @click="showModal = false" type="button" class="text-slate-400 hover:text-slate-600 p-1.5 rounded-lg hover:bg-slate-200 transition">
                                                            <i class="fa-solid fa-xmark text-lg"></i>
                                                        </button>
                                                    </div>

                                                    <!-- Modal Body (User-Friendly Payload Grid) -->
                                                    <div x-data="{ showRawJson: false }" class="p-6 space-y-6 max-h-[70vh] overflow-y-auto">
                                                        <div class="flex items-center justify-between text-xs text-slate-500 bg-slate-50 p-3 rounded-xl border border-slate-200">
                                                            <span><i class="fa-regular fa-calendar-check mr-1.5 text-indigo-600"></i> {{ $sub->submitted_at ? $sub->submitted_at->format('M d, Y h:i A') : 'N/A' }}</span>
                                                            <span><i class="fa-solid fa-network-wired mr-1.5 text-indigo-600"></i> IP: {{ $sub->ip_address }}</span>
                                                        </div>

                                                        <div x-show="!showRawJson" class="space-y-4">
                                                            <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider">Submitted Form Fields</h4>
                                                            <div class="divide-y divide-slate-100 border border-slate-200 rounded-2xl overflow-hidden">
                                                                @foreach ($payload as $fieldKey => $fieldVal)
                                                                    <div class="p-4 bg-white flex flex-col sm:flex-row sm:items-center justify-between gap-3 hover:bg-slate-50/50 transition">
                                                                        <div class="sm:w-1/3">
                                                                            <span class="font-semibold text-xs text-slate-900 block">{{ $fieldMap[$fieldKey] ?? ucfirst(str_replace('_', ' ', $fieldKey)) }}</span>
                                                                            <span class="text-[10px] text-slate-400 font-mono">{{ $fieldKey }}</span>
                                                                        </div>

                                                                        <div class="sm:w-2/3 text-xs text-slate-800">
                                                                            <!-- File Upload Field Handling -->
                                                                            @if (is_array($fieldVal) && (isset($fieldVal['is_file']) || isset($fieldVal['url'])))
                                                                                <div class="p-3 bg-indigo-50/60 border border-indigo-200 rounded-xl flex items-center justify-between">
                                                                                    <div class="flex items-center space-x-3 overflow-hidden">
                                                                                        <div class="w-8 h-8 rounded-lg bg-indigo-600 flex items-center justify-center text-white font-bold text-sm shadow-sm">
                                                                                            <i class="fa-solid fa-file-arrow-down"></i>
                                                                                        </div>
                                                                                        <div class="truncate">
                                                                                            <p class="font-bold text-slate-900 truncate">{{ $fieldVal['original_name'] ?? 'Uploaded Document' }}</p>
                                                                                            <p class="text-[10px] text-slate-500 font-mono">{{ $fieldVal['size_kb'] ?? 0 }} KB</p>
                                                                                        </div>
                                                                                    </div>
                                                                                    @if (!empty($fieldVal['url']))
                                                                                        <a href="{{ $fieldVal['url'] }}" target="_blank" class="px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-semibold text-xs shadow-sm transition flex items-center space-x-1 shrink-0">
                                                                                            <i class="fa-solid fa-download"></i>
                                                                                            <span>Download</span>
                                                                                        </a>
                                                                                    @endif
                                                                                </div>

                                                                            <!-- Array / Checkbox Selections Handling -->
                                                                            @elseif (is_array($fieldVal))
                                                                                <div class="flex flex-wrap gap-1.5">
                                                                                    @foreach ($fieldVal as $item)
                                                                                        <span class="px-2.5 py-1 bg-slate-100 text-slate-800 rounded-lg border border-slate-200 font-medium text-xs">{{ $item }}</span>
                                                                                    @endforeach
                                                                                </div>

                                                                            <!-- Rating Stars Handling -->
                                                                            @elseif (is_numeric($fieldVal) && str_contains(strtolower($fieldKey), 'rating'))
                                                                                <div class="flex items-center space-x-1 text-amber-400">
                                                                                    @for ($r = 1; $r <= 5; $r++)
                                                                                        <i class="fa-solid fa-star {{ $r <= (int)$fieldVal ? 'text-amber-400' : 'text-slate-200' }}"></i>
                                                                                    @endfor
                                                                                    <span class="text-xs font-bold text-slate-700 ml-2">{{ $fieldVal }} / 5</span>
                                                                                </div>

                                                                            <!-- Plain Text Handling -->
                                                                            @else
                                                                                <span class="bg-slate-50 px-3 py-1.5 rounded-lg border border-slate-200 block text-slate-900 font-medium">
                                                                                    {{ $fieldVal ?: '—' }}
                                                                                </span>
                                                                            @endif
                                                                        </div>
                                                                    </div>
                                                                @endforeach
                                                            </div>
                                                        </div>

                                                        <!-- Raw JSON Toggle View -->
                                                        <div x-show="showRawJson" class="space-y-2">
                                                            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Raw Payload JSON</span>
                                                            <pre class="bg-slate-900 p-4 rounded-2xl border border-slate-800 text-emerald-400 font-mono text-xs leading-relaxed overflow-x-auto">{{ json_encode($payload, JSON_PRETTY_PRINT) }}</pre>
                                                        </div>

                                                        <div class="pt-2 flex items-center justify-between border-t border-slate-100">
                                                            <button @click="showRawJson = !showRawJson" type="button" class="text-xs font-medium text-indigo-600 hover:text-indigo-800 transition">
                                                                <span x-show="!showRawJson"><i class="fa-solid fa-code mr-1"></i> View Raw JSON Payload</span>
                                                                <span x-show="showRawJson"><i class="fa-solid fa-table-cells mr-1"></i> View User-Friendly Data</span>
                                                            </button>
                                                            <button @click="showModal = false" type="button" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded-xl text-xs transition">
                                                                Close
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="p-4 border-t border-slate-200">
                    {{ $submissions->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
