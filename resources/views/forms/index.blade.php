<x-app-layout>
    <x-slot name="title">Dashboard — FormCraft AI</x-slot>

    <div class="space-y-6">
        <!-- Header Banner -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-white border border-slate-200 p-6 rounded-2xl shadow-sm">
            <div>
                <h1 class="text-2xl font-bold text-slate-900">Forms Dashboard</h1>
                <p class="text-slate-500 text-sm mt-1">Manage visual forms, generate with AI prompts, and track responses.</p>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <a href="{{ route('import.upload') }}" class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-medium border border-slate-200 transition flex items-center space-x-2">
                    <i class="fa-solid fa-file-word text-blue-600"></i>
                    <span>Import Word/Excel</span>
                </a>
                <a href="{{ route('forms.create') }}" class="px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold shadow-sm transition flex items-center space-x-2">
                    <i class="fa-solid fa-wand-magic-sparkles"></i>
                    <span>Create / Generate Form</span>
                </a>
            </div>
        </div>

        <!-- Forms Table Card -->
        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
            <div class="p-6 border-b border-slate-200 flex items-center justify-between">
                <h2 class="text-lg font-semibold text-slate-900">Active Forms</h2>
                <span class="px-3 py-1 bg-indigo-50 text-indigo-700 rounded-full text-xs font-semibold border border-indigo-100">
                    {{ $forms->total() }} Total Forms
                </span>
            </div>

            @if ($forms->isEmpty())
                <div class="p-12 text-center">
                    <div class="w-16 h-16 rounded-full bg-slate-100 text-indigo-600 flex items-center justify-center mx-auto text-2xl mb-4">
                        <i class="fa-solid fa-folder-open"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-slate-900 mb-1">No forms created yet</h3>
                    <p class="text-slate-500 text-sm max-w-sm mx-auto mb-6">Build your first form visually, generate one from a prompt, or import a Word/Excel document.</p>
                    <a href="{{ route('forms.create') }}" class="inline-flex items-center px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium transition shadow-sm">
                        <i class="fa-solid fa-plus mr-2"></i> Create First Form
                    </a>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-slate-700">
                        <thead class="bg-slate-50 text-slate-500 uppercase text-xs font-semibold tracking-wider border-b border-slate-200">
                            <tr>
                                <th class="px-6 py-4">Title & Slug</th>
                                <th class="px-6 py-4">Version</th>
                                <th class="px-6 py-4">Submissions</th>
                                <th class="px-6 py-4">Public URL</th>
                                <th class="px-6 py-4 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200">
                            @foreach ($forms as $form)
                                <tr class="hover:bg-slate-50/80 transition">
                                    <td class="px-6 py-4">
                                        <a href="{{ route('forms.edit', $form->id) }}" class="font-semibold text-slate-900 hover:text-indigo-600 transition">
                                            {{ $form->title }}
                                        </a>
                                        <p class="text-slate-500 text-xs mt-0.5">{{ Str::limit($form->description, 60) ?: 'No description provided' }}</p>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="px-2.5 py-1 rounded-md bg-slate-100 text-slate-700 text-xs font-mono border border-slate-200">
                                            v{{ $form->current_version }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <a href="{{ route('forms.submissions', $form->id) }}" class="inline-flex items-center text-xs font-semibold text-indigo-600 hover:underline">
                                            <i class="fa-solid fa-inbox mr-1.5"></i>
                                            {{ $form->submissions()->count() }} Entries
                                        </a>
                                    </td>
                                    <td class="px-6 py-4">
                                        <a href="{{ route('forms.public', $form->public_slug) }}" target="_blank" class="inline-flex items-center text-xs text-emerald-700 hover:underline bg-emerald-50 px-3 py-1.5 rounded-lg border border-emerald-200 font-medium">
                                            <i class="fa-solid fa-arrow-up-right-from-square mr-1.5"></i> Fill Form
                                        </a>
                                    </td>
                                    <td class="px-6 py-4 text-right space-x-2">
                                        <a href="{{ route('forms.edit', $form->id) }}" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-medium border border-slate-200 transition">
                                            <i class="fa-solid fa-pen-to-square"></i> Edit
                                        </a>
                                        <a href="{{ route('forms.versions', $form->id) }}" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-medium border border-slate-200 transition">
                                            <i class="fa-solid fa-clock-rotate-left"></i> History
                                        </a>
                                        <a href="{{ route('forms.analytics', $form->id) }}" class="px-3 py-1.5 bg-purple-50 hover:bg-purple-100 text-purple-700 rounded-lg text-xs font-medium border border-purple-200 transition">
                                            <i class="fa-solid fa-chart-line"></i> Analytics
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="p-4 border-t border-slate-200">
                    {{ $forms->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
