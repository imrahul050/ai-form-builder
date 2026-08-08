<x-app-layout>
    <x-slot name="title">Analytics: {{ $form->title }}</x-slot>

    <div class="space-y-6">
        <div class="bg-white border border-slate-200 p-6 rounded-2xl shadow-sm">
            <h1 class="text-2xl font-bold text-slate-900">Completion & Drop-off Analytics</h1>
            <p class="text-slate-500 text-sm mt-1">Form: <span class="text-indigo-600 font-semibold">{{ $form->title }}</span></p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div class="bg-white border border-slate-200 p-6 rounded-2xl shadow-sm">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Total Views</span>
                <p class="text-3xl font-extrabold text-slate-900 mt-2">{{ number_format($totalViews) }}</p>
            </div>
            <div class="bg-white border border-slate-200 p-6 rounded-2xl shadow-sm">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Submissions</span>
                <p class="text-3xl font-extrabold text-emerald-600 mt-2">{{ number_format($totalSubmits) }}</p>
            </div>
            <div class="bg-white border border-slate-200 p-6 rounded-2xl shadow-sm">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Abandons</span>
                <p class="text-3xl font-extrabold text-rose-600 mt-2">{{ number_format($totalAbandons) }}</p>
            </div>
            <div class="bg-white border border-slate-200 p-6 rounded-2xl shadow-sm">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Completion Rate</span>
                <p class="text-3xl font-extrabold text-purple-600 mt-2">{{ $completionRate }}%</p>
            </div>
        </div>
    </div>
</x-app-layout>
