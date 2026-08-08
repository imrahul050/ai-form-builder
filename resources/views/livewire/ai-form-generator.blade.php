<div class="bg-white border border-indigo-100 p-6 rounded-2xl shadow-sm space-y-4 relative overflow-hidden">
    <div class="flex items-center space-x-3">
        <div class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold">
            <i class="fa-solid fa-brain"></i>
        </div>
        <div>
            <h3 class="text-base font-bold text-slate-900">AI Form Assistant</h3>
            <p class="text-xs text-slate-500">Generate a complete form from scratch or modify the active form schema with natural language.</p>
        </div>
    </div>

    @if ($form)
        <!-- Existing Form Modifier Prompt -->
        <div class="flex gap-3">
            <input type="text" wire:model="editPrompt" wire:keydown.enter="modifyForm" class="flex-1 bg-slate-50 border border-slate-200 focus:border-indigo-600 focus:bg-white rounded-xl px-4 py-2.5 text-sm text-slate-900 focus:outline-none placeholder-slate-400" placeholder="e.g. 'Add emergency contact section', 'make phone required', 'translate to Hindi'...">
            <button wire:click="modifyForm" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-semibold transition flex items-center space-x-2 shadow-sm">
                <i class="fa-solid fa-wand-magic-sparkles"></i>
                <span>Modify Form</span>
            </button>
        </div>
    @else
        <!-- New Form Creation Prompt -->
        <div class="flex gap-3">
            <input type="text" wire:model="prompt" wire:keydown.enter="generateFromPrompt" class="flex-1 bg-slate-50 border border-slate-200 focus:border-indigo-600 focus:bg-white rounded-xl px-4 py-2.5 text-sm text-slate-900 focus:outline-none placeholder-slate-400" placeholder="e.g. 'Internship application with education history, skills, and resume upload'...">
            <button wire:click="generateFromPrompt" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-semibold shadow-sm transition flex items-center space-x-2">
                <i class="fa-solid fa-bolt"></i>
                <span>Generate Form</span>
            </button>
        </div>
    @endif

    <!-- Status & Latency Feedback -->
    @if ($isGenerating)
        <div class="p-3 bg-indigo-50 border border-indigo-200 rounded-xl text-xs text-indigo-800 flex items-center space-x-2 animate-pulse">
            <i class="fa-solid fa-spinner fa-spin text-indigo-600"></i>
            <span>{{ $statusMessage }}</span>
        </div>
    @endif

    @if ($generatedSchema && !$form)
        <div class="mt-4 p-4 bg-slate-50 border border-slate-200 rounded-xl space-y-3">
            <div class="flex items-center justify-between text-xs text-slate-600">
                <span class="font-bold text-slate-900">Generated Form Preview: "{{ $generatedSchema['title'] ?? 'Form' }}"</span>
                <span>Latency: {{ $latencyMs }}ms | Tokens: {{ $tokenCount }}</span>
            </div>
            <button wire:click="saveAsNewForm" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-semibold transition shadow-sm">
                <i class="fa-solid fa-check mr-1.5"></i> Save Generated Form
            </button>
        </div>
    @endif
</div>
