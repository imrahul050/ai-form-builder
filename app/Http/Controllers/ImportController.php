<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessDocumentImportJob;
use App\Models\Form;
use App\Models\ImportJob;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as ExcelWriter;

class ImportController extends Controller
{
    public function showUpload()
    {
        if (!session()->has('tenant_id')) {
            $tenant = Tenant::firstOrCreate(['slug' => 'default-tenant'], ['name' => 'Default Organization']);
            session(['tenant_id' => $tenant->id]);
        }
        return view('import.upload');
    }

    public function upload(Request $request)
    {
        $request->validate([
            'document' => 'required|file|mimes:docx,xlsx|max:10240',
        ]);

        $file = $request->file('document');
        $ext = strtolower($file->getClientOriginalExtension());
        $path = $file->store('imports', 'local');

        $job = ImportJob::create([
            'tenant_id' => session('tenant_id', 1),
            'file_name' => $file->getClientOriginalName(),
            'file_path' => $path,
            'file_type' => $ext === 'docx' ? 'docx' : 'xlsx',
            'status' => 'uploaded',
        ]);

        ProcessDocumentImportJob::dispatchSync($job->id);

        return redirect()->route('import.preview', $job->id);
    }

    /**
     * Instantly imports a demo Excel spreadsheet without requiring manual file upload.
     */
    public function loadDemoExcel(Request $request)
    {
        if (!session()->has('tenant_id')) {
            $tenant = Tenant::firstOrCreate(['slug' => 'default-tenant'], ['name' => 'Default Organization']);
            session(['tenant_id' => $tenant->id]);
        }

        $sampleDir = storage_path('app/samples');
        if (!file_exists($sampleDir)) {
            mkdir($sampleDir, 0777, true);
        }

        $samplePath = $sampleDir . '/demo_feedback_survey.xlsx';

        if (!file_exists($samplePath)) {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('User Feedback');
            $sheet->fromArray([
                ['Full Name *', 'Email Address *', 'Satisfaction Rating (1-5)', 'Feedback Comments', 'Submission Date'],
                ['Alice Johnson', 'alice@example.com', '5', 'The AI Form builder is amazing!', '2026-08-08'],
                ['Bob Smith', 'bob@example.com', '4', 'Very easy drag and drop canvas.', '2026-08-08']
            ]);
            $writer = new ExcelWriter($spreadsheet);
            $writer->save($samplePath);
        }

        $targetRelativePath = 'imports/demo_feedback_survey_' . time() . '.xlsx';
        $targetFullPath = storage_path('app/' . $targetRelativePath);
        if (!file_exists(dirname($targetFullPath))) {
            mkdir(dirname($targetFullPath), 0777, true);
        }

        copy($samplePath, $targetFullPath);

        $job = ImportJob::create([
            'tenant_id' => session('tenant_id', 1),
            'file_name' => 'Demo_Feedback_Survey.xlsx',
            'file_path' => $targetRelativePath,
            'file_type' => 'xlsx',
            'status' => 'uploaded',
        ]);

        ProcessDocumentImportJob::dispatchSync($job->id);

        return redirect()->route('import.preview', $job->id)->with('status', 'Demo Excel file imported successfully!');
    }

    /**
     * Downloads a sample .xlsx template file for the user to inspect in Excel.
     */
    public function downloadSampleExcel()
    {
        $sampleDir = storage_path('app/samples');
        if (!file_exists($sampleDir)) {
            mkdir($sampleDir, 0777, true);
        }

        $samplePath = $sampleDir . '/FormCraft_Demo_Template.xlsx';

        if (!file_exists($samplePath)) {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Sample Form Template');
            $sheet->fromArray([
                ['Full Name *', 'Email Address *', 'Phone Number', 'Years of Experience', 'Date of Birth', 'Additional Comments'],
                ['Jane Doe', 'jane@example.com', '+1 (555) 019-2834', '3', '1998-05-15', 'Excited about this role!']
            ]);
            $writer = new ExcelWriter($spreadsheet);
            $writer->save($samplePath);
        }

        return response()->download($samplePath, 'FormCraft_Demo_Template.xlsx');
    }

    public function showPreview(ImportJob $importJob)
    {
        return view('import.preview', compact('importJob'));
    }

    public function commit(ImportJob $importJob, Request $request)
    {
        $schema = $request->input('schema');
        if (is_string($schema)) {
            $schema = json_decode($schema, true);
        }

        $title = $schema['title'] ?? 'Imported Form - ' . $importJob->file_name;

        $form = Form::create([
            'tenant_id' => $importJob->tenant_id,
            'title' => $title,
            'description' => 'Imported from document: ' . $importJob->file_name,
            'public_slug' => Str::slug($title) . '-' . Str::random(6),
            'schema' => $schema,
        ]);

        $importJob->update(['status' => 'committed']);

        return redirect()->route('forms.edit', $form->id)->with('status', 'Form imported and saved successfully!');
    }
}
