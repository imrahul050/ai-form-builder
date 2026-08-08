<x-app-layout>
    <x-slot name="title">Version History: {{ $form->title }}</x-slot>

    <div class="space-y-6">
        <div class="bg-white border border-slate-200 p-6 rounded-2xl shadow-sm">
            <h1 class="text-2xl font-bold text-slate-900">Form Versioning & Audit History</h1>
            <p class="text-slate-500 text-sm mt-1">Form: <span class="text-indigo-600 font-semibold">{{ $form->title }}</span> (Current Active: v{{ $form->current_version }})</p>
        </div>

        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
            <div class="p-6">
                <div class="space-y-4">
                    @foreach ($versions as $ver)
                        <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 flex items-center justify-between">
                            <div class="space-y-1">
                                <div class="flex items-center space-x-3">
                                    <span class="px-2.5 py-0.5 rounded bg-indigo-50 text-indigo-700 font-mono font-bold text-xs border border-indigo-200">v{{ $ver->version_number }}</span>
                                    <span class="text-sm font-semibold text-slate-900">{{ $ver->change_summary ?: 'Version Snapshot' }}</span>
                                    @if ($ver->version_number === $form->current_version)
                                        <span class="px-2 py-0.5 bg-emerald-50 text-emerald-700 border border-emerald-200 rounded text-[10px] font-bold">Active</span>
                                    @endif
                                </div>
                                <p class="text-xs text-slate-500">Saved on {{ $ver->created_at ? $ver->created_at->format('M d, Y H:i A') : 'N/A' }}</p>
                            </div>

                            @if ($ver->version_number !== $form->current_version)
                                <form action="{{ route('forms.versions.rollback', [$form->id, $ver->id]) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="px-4 py-2 bg-amber-50 hover:bg-amber-100 text-amber-800 border border-amber-200 text-xs font-semibold rounded-xl transition">
                                        <i class="fa-solid fa-rotate-left mr-1.5"></i> Rollback to v{{ $ver->version_number }}
                                    </button>
                                </form>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
