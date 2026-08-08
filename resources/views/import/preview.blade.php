<x-app-layout>
    <x-slot name="title">Import Preview & Mapping</x-slot>

    <div class="space-y-6">
        <div class="bg-white border border-slate-200 p-6 rounded-2xl shadow-sm flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-slate-900">Import Preview & Mapping</h1>
                <p class="text-slate-500 text-sm mt-1">Review extracted fields from <span class="text-indigo-600 font-semibold">{{ $importJob->file_name }}</span> before committing to live form.</p>
            </div>

            <form action="{{ route('import.commit', $importJob->id) }}" method="POST">
                @csrf
                <input type="hidden" name="schema" value="{{ json_encode($importJob->extracted_structure) }}">
                <button type="submit" class="px-6 py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-sm rounded-xl shadow-sm transition">
                    <i class="fa-solid fa-check mr-2"></i> Commit & Create Form
                </button>
            </form>
        </div>

        <div class="bg-white border border-slate-200 p-6 rounded-2xl shadow-sm space-y-4">
            <h2 class="text-lg font-bold text-slate-900">Extracted Form Schema Preview</h2>
            <pre class="bg-slate-900 p-4 rounded-xl border border-slate-800 text-xs font-mono text-emerald-400 overflow-x-auto max-h-[500px]">{{ json_encode($importJob->extracted_structure, JSON_PRETTY_PRINT) }}</pre>
        </div>
    </div>
</x-app-layout>
