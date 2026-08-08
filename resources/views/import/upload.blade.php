<x-app-layout>
    <x-slot name="title">Import Word / Excel Document</x-slot>

    <div class="max-w-3xl mx-auto space-y-6">
        <!-- How Excel Import Works Guide -->
        <div class="bg-white border border-slate-200 p-6 rounded-2xl shadow-sm space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div class="flex items-center space-x-2 text-indigo-600 font-bold text-base">
                    <i class="fa-solid fa-file-excel text-emerald-600 text-xl"></i>
                    <span>How to Format your Excel Spreadsheet (.xlsx)</span>
                </div>
                <a href="{{ route('import.download_sample') }}" class="px-3.5 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-lg border border-slate-200 transition flex items-center space-x-1.5">
                    <i class="fa-solid fa-download text-indigo-600"></i>
                    <span>Download Sample Template (.xlsx)</span>
                </a>
            </div>

            <div class="text-xs text-slate-600 space-y-2">
                <p><strong class="text-slate-900">1. Row 1 (Header Row):</strong> Each cell in Row 1 becomes a <span class="font-semibold text-slate-900">Form Field Label</span>. Add an asterisk <code class="bg-rose-50 text-rose-600 font-bold px-1 rounded">*</code> (e.g. <code class="bg-slate-100 px-1 rounded">Full Name *</code>) to mark a field as required.</p>
                <p><strong class="text-slate-900">2. Row 2 (Sample Data):</strong> Sample values are evaluated by the AI & parser to auto-detect the field type (Email, Date, Number, Phone, or Text).</p>
            </div>

            <!-- Visual Table Example -->
            <div class="overflow-x-auto border border-slate-200 rounded-xl">
                <table class="w-full text-left text-xs">
                    <thead class="bg-emerald-50 text-emerald-900 font-bold border-b border-emerald-200">
                        <tr>
                            <th class="px-3 py-2 border-r border-emerald-200">Full Name *</th>
                            <th class="px-3 py-2 border-r border-emerald-200">Email Address *</th>
                            <th class="px-3 py-2 border-r border-emerald-200">Phone Number</th>
                            <th class="px-3 py-2 border-r border-emerald-200">Date of Birth</th>
                            <th class="px-3 py-2">Additional Comments</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white text-slate-600 divide-y divide-slate-100">
                        <tr>
                            <td class="px-3 py-2 border-r border-slate-100">Jane Doe</td>
                            <td class="px-3 py-2 border-r border-slate-100">jane@example.com</td>
                            <td class="px-3 py-2 border-r border-slate-100">+1 (555) 019-2834</td>
                            <td class="px-3 py-2 border-r border-slate-100">1998-05-15</td>
                            <td class="px-3 py-2">Excited about this role!</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <p class="text-[11px] text-slate-400 italic">Result: The table above automatically generates a form with Text, Email, Phone, Date Picker, and Text Area fields.</p>
        </div>

        <!-- Quick Demo Excel Option Card -->
        <div class="bg-gradient-to-r from-emerald-50 to-teal-50 border border-emerald-200 p-6 rounded-2xl shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="space-y-1">
                <div class="flex items-center space-x-2 text-emerald-900 font-bold text-base">
                    <i class="fa-solid fa-bolt text-emerald-600 text-lg"></i>
                    <span>Test Instant Demo Excel Import</span>
                </div>
                <p class="text-xs text-emerald-700">Click below to test the spreadsheet importer instantly with sample data—no file selection needed.</p>
            </div>
            <form action="{{ route('import.demo_excel') }}" method="POST">
                @csrf
                <button type="submit" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs rounded-xl shadow-sm transition flex items-center space-x-2 whitespace-nowrap">
                    <i class="fa-solid fa-wand-magic-sparkles"></i>
                    <span>Try Demo Excel File</span>
                </button>
            </form>
        </div>

        <!-- File Upload Card -->
        <div class="bg-white border border-slate-200 p-8 rounded-2xl shadow-sm space-y-6">
            <div>
                <h2 class="text-xl font-bold text-slate-900">Upload Your Own Document (.docx / .xlsx)</h2>
                <p class="text-slate-500 text-sm mt-1">Upload your customized Word document or Excel spreadsheet to extract headings, questions, and fields.</p>
            </div>

            <form action="{{ route('import.upload.post') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf
                <div class="p-8 border-2 border-dashed border-slate-300 hover:border-indigo-500 rounded-2xl text-center bg-slate-50 transition">
                    <i class="fa-solid fa-cloud-arrow-up text-4xl text-indigo-600 mb-3"></i>
                    <p class="text-sm font-semibold text-slate-900">Choose a Word (.docx) or Excel (.xlsx) file</p>
                    <p class="text-xs text-slate-500 mt-1">Maximum file size: 10MB</p>
                    <input type="file" name="document" required accept=".docx,.xlsx" class="mt-4 block w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-indigo-600 file:text-white hover:file:bg-indigo-700 cursor-pointer">
                </div>

                @error('document')
                    <p class="text-rose-600 text-xs">{{ $message }}</p>
                @enderror

                <button type="submit" class="w-full py-3.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl shadow-sm transition">
                    Upload & Process Document
                </button>
            </form>
        </div>
    </div>
</x-app-layout>
