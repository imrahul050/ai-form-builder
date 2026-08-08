<?php

namespace App\Livewire;

use App\Models\AiGenerationLog;
use App\Models\Form;
use App\Services\LlmService;
use Livewire\Component;
use Illuminate\Support\Str;

class AiFormGenerator extends Component
{
    public string $prompt = '';
    public string $editPrompt = '';
    public bool $isGenerating = false;
    public string $statusMessage = '';
    public ?Form $form = null;
    public ?array $generatedSchema = null;
    public int $tokenCount = 0;
    public int $latencyMs = 0;

    public function mount(?Form $form = null)
    {
        $this->form = $form;
    }

    public function generateFromPrompt(LlmService $llmService)
    {
        $this->validate([
            'prompt' => 'required|string|min:5|max:1000',
        ]);

        $this->isGenerating = true;
        $this->statusMessage = 'Analyzing prompt and generating schema structure...';

        $result = $llmService->generateFormFromPrompt($this->prompt);

        $this->generatedSchema = $result['schema'];
        $this->latencyMs = $result['latency_ms'] ?? 180;
        $this->tokenCount = $result['token_count'] ?? 410;

        AiGenerationLog::create([
            'tenant_id' => session('tenant_id', 1),
            'job_id' => 'job_' . Str::random(10),
            'prompt' => $this->prompt,
            'mode' => 'create',
            'model' => $result['model'] ?? 'gpt-4o-mini',
            'raw_response' => json_encode($result['schema']),
            'token_count' => $this->tokenCount,
            'latency_ms' => $this->latencyMs,
            'status' => 'completed',
        ]);

        $this->isGenerating = false;
        $this->statusMessage = 'Form generated successfully!';
    }

    public function modifyForm(LlmService $llmService)
    {
        if (!$this->form) return;

        $this->validate([
            'editPrompt' => 'required|string|min:3|max:1000',
        ]);

        $this->isGenerating = true;
        $this->statusMessage = 'Applying AI modifications...';

        $result = $llmService->modifyExistingForm($this->form->schema, $this->editPrompt);

        $this->form->update([
            'schema' => $result['schema'],
            'current_version' => $this->form->current_version + 1,
        ]);

        AiGenerationLog::create([
            'tenant_id' => session('tenant_id', 1),
            'form_id' => $this->form->id,
            'job_id' => 'job_' . Str::random(10),
            'prompt' => $this->editPrompt,
            'mode' => 'edit',
            'model' => $result['model'] ?? 'gpt-4o-mini',
            'raw_response' => json_encode($result['schema']),
            'token_count' => $result['token_count'] ?? 380,
            'latency_ms' => $result['latency_ms'] ?? 160,
            'status' => 'completed',
        ]);

        $this->isGenerating = false;
        $this->editPrompt = '';
        return redirect()->route('forms.edit', $this->form->id)->with('status', 'Form updated via AI prompt!');
    }

    public function saveAsNewForm()
    {
        if (!$this->generatedSchema) return;

        $title = $this->generatedSchema['title'] ?? 'AI Form - ' . substr($this->prompt, 0, 20);

        $form = Form::create([
            'tenant_id' => session('tenant_id', 1),
            'title' => $title,
            'description' => 'Created via AI prompt: ' . $this->prompt,
            'public_slug' => Str::slug($title) . '-' . Str::random(6),
            'schema' => $this->generatedSchema,
        ]);

        return redirect()->route('forms.edit', $form->id)->with('status', 'AI Form saved!');
    }

    public function render()
    {
        return view('livewire.ai-form-generator');
    }
}
