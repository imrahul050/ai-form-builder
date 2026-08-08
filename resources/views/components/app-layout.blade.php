<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-50 text-slate-900">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'AI-Powered Form Builder' }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    @livewireStyles
</head>
<body class="h-full flex flex-col font-sans antialiased bg-slate-50 text-slate-900 selection:bg-indigo-500 selection:text-white">
    <!-- Top Navigation Bar -->
    <nav class="bg-white border-b border-slate-200 sticky top-0 z-50 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <div class="flex items-center space-x-3">
                    <a href="{{ route('forms.index') }}" class="flex items-center space-x-3 group">
                        <div class="w-10 h-10 rounded-xl bg-indigo-600 flex items-center justify-center text-white font-bold text-xl shadow-md shadow-indigo-200 group-hover:bg-indigo-700 transition">
                            <i class="fa-solid fa-wand-magic-sparkles"></i>
                        </div>
                        <span class="font-extrabold text-xl tracking-tight text-slate-900 group-hover:text-indigo-600 transition">
                            FormCraft AI
                        </span>
                    </a>
                </div>

                <div class="flex items-center space-x-3">
                    <a href="{{ route('forms.index') }}" class="px-3.5 py-2 rounded-lg text-sm font-medium text-slate-600 hover:text-slate-900 hover:bg-slate-100 transition">
                        <i class="fa-solid fa-layer-group mr-1.5 text-indigo-600"></i> My Forms
                    </a>
                    <a href="{{ route('import.upload') }}" class="px-3.5 py-2 rounded-lg text-sm font-medium text-slate-600 hover:text-slate-900 hover:bg-slate-100 transition">
                        <i class="fa-solid fa-file-import mr-1.5 text-emerald-600"></i> Import (.docx/.xlsx)
                    </a>
                    <a href="{{ route('forms.create') }}" class="px-4 py-2 rounded-xl text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 shadow-sm transition flex items-center space-x-2">
                        <i class="fa-solid fa-plus"></i>
                        <span>New Form</span>
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content Area -->
    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @if (session('status'))
            <div class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 flex items-center justify-between shadow-sm">
                <div class="flex items-center space-x-3">
                    <i class="fa-solid fa-circle-check text-emerald-600 text-lg"></i>
                    <span class="text-sm font-medium">{{ session('status') }}</span>
                </div>
            </div>
        @endif

        {{ $slot }}
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-slate-200 py-6 mt-auto">
        <div class="max-w-7xl mx-auto px-4 text-center text-slate-500 text-sm">
            <p>AI-Powered Form Builder &bull; Built with Laravel 11, Livewire 3 & MySQL 8</p>
        </div>
    </footer>

    @livewireScripts

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3500,
                timerProgressBar: true,
                didOpen: (toast) => {
                    toast.addEventListener('mouseenter', Swal.stopTimer);
                    toast.addEventListener('mouseleave', Swal.resumeTimer);
                }
            });

            @if (session('status'))
                Toast.fire({
                    icon: 'success',
                    title: @json(session('status'))
                });
            @endif

            @if (session('success_message'))
                Toast.fire({
                    icon: 'success',
                    title: @json(session('success_message'))
                });
            @endif

            @if (session('error'))
                Toast.fire({
                    icon: 'error',
                    title: @json(session('error'))
                });
            @endif

            window.addEventListener('swal:toast', event => {
                const detail = event.detail[0] || event.detail;
                Toast.fire({
                    icon: detail.icon || detail.type || 'success',
                    title: detail.title || detail.message || 'Saved successfully!'
                });
            });
        });
    </script>
</body>
</html>
