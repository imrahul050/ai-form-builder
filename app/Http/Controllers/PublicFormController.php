<?php

namespace App\Http\Controllers;

use App\Models\AnalyticsEvent;
use App\Models\Form;
use App\Models\FormSubmission;
use App\Services\SchemaValidationParser;
use Illuminate\Http\Request;

class PublicFormController extends Controller
{
    public function show(string $slug, Request $request)
    {
        $form = Form::withoutGlobalScope('tenant')->where('public_slug', $slug)->where('is_active', true)->firstOrFail();

        // Track page view event
        AnalyticsEvent::create([
            'tenant_id' => $form->tenant_id,
            'form_id' => $form->id,
            'session_id' => $request->session()->getId(),
            'event_type' => 'view',
        ]);

        return view('forms.public', compact('form'));
    }

    public function submit(string $slug, Request $request, SchemaValidationParser $parser)
    {
        $form = Form::withoutGlobalScope('tenant')->where('public_slug', $slug)->where('is_active', true)->firstOrFail();

        // Parse dynamic validation rules from schema
        $rules = $parser->parseRules($form->schema);
        $validatedData = $request->validate($rules);

        // Process file uploads to disk and save file metadata in payload
        foreach ($request->allFiles() as $fileKey => $file) {
            if ($request->hasFile($fileKey) && $request->file($fileKey)->isValid()) {
                $uploadedFile = $request->file($fileKey);
                $path = $uploadedFile->store('submissions/' . $form->id, 'public');

                $validatedData[$fileKey] = [
                    'is_file' => true,
                    'original_name' => $uploadedFile->getClientOriginalName(),
                    'path' => $path,
                    'url' => asset('storage/' . $path),
                    'mime_type' => $uploadedFile->getClientMimeType(),
                    'size_kb' => round($uploadedFile->getSize() / 1024, 2),
                ];
            }
        }

        // Store submission payload
        FormSubmission::create([
            'tenant_id' => $form->tenant_id,
            'form_id' => $form->id,
            'form_version' => $form->current_version,
            'payload' => $validatedData,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'submitted_at' => now(),
        ]);

        // Track submit event
        AnalyticsEvent::create([
            'tenant_id' => $form->tenant_id,
            'form_id' => $form->id,
            'session_id' => $request->session()->getId(),
            'event_type' => 'submit',
        ]);

        return redirect()->back()->with('success_message', 'Thank you! Your response has been recorded successfully.');
    }
}
