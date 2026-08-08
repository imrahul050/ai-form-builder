<?php

namespace App\Jobs;

use App\Models\AiGenerationLog;
use App\Models\Form;
use App\Services\LlmService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GenerateFormFromPromptJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public int $tenantId,
        public string $jobId,
        public string $prompt,
        public string $mode = 'create',
        public ?int $formId = null
    ) {}

    public function handle(LlmService $llmService): void
    {
        $log = AiGenerationLog::where('job_id', $this->jobId)->first();

        if (!$log) {
            $log = AiGenerationLog::create([
                'tenant_id' => $this->tenantId,
                'form_id' => $this->formId,
                'job_id' => $this->jobId,
                'prompt' => $this->prompt,
                'mode' => $this->mode,
                'model' => env('LLM_MODEL', 'gpt-4o-mini'),
                'status' => 'pending',
            ]);
        }

        try {
            if ($this->mode === 'edit' && $this->formId) {
                $form = Form::withoutGlobalScope('tenant')->find($this->formId);
                $existingSchema = $form ? $form->schema : [];
                $result = $llmService->modifyExistingForm($existingSchema, $this->prompt);
            } else {
                $result = $llmService->generateFormFromPrompt($this->prompt);
            }

            $log->update([
                'raw_response' => json_encode($result['schema']),
                'token_count' => $result['token_count'] ?? 0,
                'latency_ms' => $result['latency_ms'] ?? 0,
                'status' => 'completed',
            ]);

            if ($this->mode === 'edit' && isset($form) && $form) {
                $form->update([
                    'schema' => $result['schema'],
                    'current_version' => $form->current_version + 1,
                ]);
            }

        } catch (\Exception $e) {
            $log->update([
                'status' => 'failed',
                'error_log' => $e->getMessage(),
            ]);
        }
    }
}
