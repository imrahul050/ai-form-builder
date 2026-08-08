<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-50 text-slate-900">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $form->title }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="min-h-full py-12 px-4 sm:px-6 lg:px-8 font-sans antialiased bg-slate-50 text-slate-900">
    <div class="max-w-2xl mx-auto space-y-6">
        <!-- Success Alert -->
        @if (session('success_message'))
            <div class="p-6 bg-emerald-50 border border-emerald-200 text-emerald-900 rounded-2xl shadow-sm text-center space-y-2">
                <i class="fa-solid fa-circle-check text-4xl text-emerald-600"></i>
                <h2 class="text-xl font-bold text-slate-900">Response Submitted</h2>
                <p class="text-sm text-slate-600">{{ session('success_message') }}</p>
            </div>
        @else
            <!-- Form Header Card -->
            <div class="bg-white border border-slate-200 p-8 rounded-2xl shadow-sm space-y-3">
                <div class="flex items-center space-x-2 text-xs font-semibold text-indigo-600 uppercase tracking-wider">
                    <i class="fa-solid fa-clipboard-check"></i>
                    <span>Public Application Form</span>
                </div>
                <h1 class="text-3xl font-extrabold text-slate-900">{{ $form->title }}</h1>
                @if ($form->description)
                    <p class="text-slate-600 text-sm leading-relaxed">{{ $form->description }}</p>
                @endif
            </div>

            <!-- Form Submission Body -->
            <form action="{{ route('forms.public.submit', $form->public_slug) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf

                @php $schema = $form->schema; @endphp

                @foreach ($schema['sections'] ?? [] as $section)
                    <div class="bg-white border border-slate-200 p-6 rounded-2xl shadow-sm space-y-5">
                        <div class="border-b border-slate-200 pb-3">
                            <h2 class="text-lg font-bold text-slate-900">{{ $section['title'] }}</h2>
                            @if (!empty($section['description']))
                                <p class="text-xs text-slate-500 mt-0.5">{{ $section['description'] }}</p>
                            @endif
                        </div>

                        <div class="space-y-4">
                            @foreach ($section['fields'] ?? [] as $field)
                                @php $key = $field['key']; @endphp
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1.5">
                                        {{ $field['label'] }}
                                        @if (!empty($field['required']))
                                            <span class="text-rose-500 font-bold">*</span>
                                        @endif
                                    </label>

                                    @if ($field['type'] === 'textarea')
                                        <textarea name="{{ $key }}" rows="4" class="w-full bg-slate-50 border border-slate-200 rounded-xl p-3 text-sm text-slate-900 focus:border-indigo-600 focus:bg-white focus:outline-none" placeholder="{{ $field['placeholder'] ?? '' }}">{{ old($key) }}</textarea>
                                    @elseif ($field['type'] === 'dropdown')
                                        <select name="{{ $key }}" class="w-full bg-slate-50 border border-slate-200 rounded-xl p-3 text-sm text-slate-900 focus:border-indigo-600 focus:bg-white focus:outline-none">
                                            <option value="">Select option...</option>
                                            @foreach ($field['options'] ?? [] as $opt)
                                                <option value="{{ $opt['value'] }}" {{ old($key) == $opt['value'] ? 'selected' : '' }}>{{ $opt['label'] }}</option>
                                            @endforeach
                                        </select>
                                    @elseif ($field['type'] === 'radio')
                                        <div class="space-y-2 pt-1">
                                            @foreach ($field['options'] ?? [] as $opt)
                                                <label class="flex items-center space-x-2 text-sm text-slate-700 cursor-pointer">
                                                    <input type="radio" name="{{ $key }}" value="{{ $opt['value'] }}" {{ old($key) == $opt['value'] ? 'checked' : '' }} class="bg-slate-50 border-slate-300 text-indigo-600 focus:ring-indigo-500">
                                                    <span>{{ $opt['label'] }}</span>
                                                </label>
                                            @endforeach
                                        </div>
                                    @elseif ($field['type'] === 'checkbox')
                                        <div class="space-y-2 pt-1">
                                            @foreach ($field['options'] ?? [] as $opt)
                                                <label class="flex items-center space-x-2 text-sm text-slate-700 cursor-pointer">
                                                    <input type="checkbox" name="{{ $key }}[]" value="{{ $opt['value'] }}" {{ (is_array(old($key)) && in_array($opt['value'], old($key))) ? 'checked' : '' }} class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                                                    <span>{{ $opt['label'] }}</span>
                                                </label>
                                            @endforeach
                                        </div>
                                    @elseif ($field['type'] === 'rating')
                                        <div x-data="{ rating: {{ old($key, 0) }}, hoverRating: 0 }" class="flex items-center space-x-2 pt-1">
                                            <input type="hidden" name="{{ $key }}" :value="rating">
                                            @for ($i = 1; $i <= 5; $i++)
                                                <button type="button"
                                                    @click="rating = {{ $i }}"
                                                    @mouseenter="hoverRating = {{ $i }}"
                                                    @mouseleave="hoverRating = 0"
                                                    class="focus:outline-none transition transform hover:scale-110">
                                                    <i class="fa-solid fa-star text-2xl transition-colors duration-150"
                                                       :class="(hoverRating ? hoverRating >= {{ $i }} : rating >= {{ $i }}) ? 'text-amber-400' : 'text-slate-300'"></i>
                                                </button>
                                            @endfor
                                            <span x-show="rating > 0" x-text="rating + ' / 5'" class="text-xs font-bold text-slate-500 ml-2"></span>
                                        </div>
                                    @elseif ($field['type'] === 'file')
                                        <input type="file" name="{{ $key }}" class="w-full bg-slate-50 border border-slate-200 rounded-xl p-2.5 text-xs text-slate-600 file:mr-4 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-indigo-600 file:text-white hover:file:bg-indigo-700 cursor-pointer">
                                    @else
                                        <input type="{{ $field['type'] === 'phone' ? 'tel' : $field['type'] }}" name="{{ $key }}" value="{{ old($key) }}" class="w-full bg-slate-50 border border-slate-200 rounded-xl p-3 text-sm text-slate-900 focus:border-indigo-600 focus:bg-white focus:outline-none" placeholder="{{ $field['placeholder'] ?? '' }}">
                                    @endif

                                    @error($key)
                                        <p class="text-rose-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach

                <div class="pt-2">
                    <button type="submit" class="w-full py-3.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-2xl shadow-sm transition">
                        {{ $schema['settings']['submit_label'] ?? 'Submit Form' }}
                    </button>
                </div>
            </form>
        @endif
    </div>
</body>
</html>
