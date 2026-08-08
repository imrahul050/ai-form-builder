<?php

namespace App\Jobs;

use App\Models\ImportJob;
use App\Services\DocxParserService;
use App\Services\XlsxParserService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessDocumentImportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $importJobId) {}

    public function handle(DocxParserService $docxParser, XlsxParserService $xlsxParser): void
    {
        $job = ImportJob::withoutGlobalScope('tenant')->find($this->importJobId);
        if (!$job) return;

        $job->update(['status' => 'parsing']);

        try {
            $filePath = storage_path('app/private/' . $job->file_path);
            if (!file_exists($filePath)) {
                $filePath = storage_path('app/' . $job->file_path);
            }

            if ($job->file_type === 'docx') {
                $parsed = $docxParser->parse($filePath);
            } else {
                $parsed = $xlsxParser->parse($filePath);
            }

            $job->update([
                'status' => 'preview_ready',
                'extracted_structure' => $parsed,
                'mapping_schema' => $parsed,
            ]);

        } catch (\Exception $e) {
            $job->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);
        }
    }
}
