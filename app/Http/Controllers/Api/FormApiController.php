<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Form;
use App\Services\SchemaValidationParser;
use Illuminate\Http\Request;

class FormApiController extends Controller
{
    /**
     * Public API endpoint to fetch form schema metadata.
     */
    public function getSchema(string $slug)
    {
        $form = Form::withoutGlobalScope('tenant')->where('public_slug', $slug)->where('is_active', true)->first();

        if (!$form) {
            return response()->json(['error' => 'Form not found or inactive'], 404);
        }

        return response()->json([
            'id' => $form->uuid,
            'title' => $form->title,
            'description' => $form->description,
            'public_slug' => $form->public_slug,
            'current_version' => $form->current_version,
            'schema' => $form->schema,
        ]);
    }

    /**
     * Public REST API endpoint for programmatic form submission.
     */
    public function submitApi(string $slug, Request $request, SchemaValidationParser $parser)
    {
        $form = Form::withoutGlobalScope('tenant')->where('public_slug', $slug)->where('is_active', true)->first();

        if (!$form) {
            return response()->json(['error' => 'Form not found or inactive'], 404);
        }

        $rules = $parser->parseRules($form->schema);
        $validator = validator($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Validation failed',
                'messages' => $validator->errors(),
            ], 422);
        }

        $submission = $form->submissions()->create([
            'tenant_id' => $form->tenant_id,
            'form_version' => $form->current_version,
            'payload' => $validator->validated(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'submitted_at' => now(),
        ]);

        return response()->json([
            'status' => 'success',
            'submission_id' => $submission->submission_uuid,
            'submitted_at' => $submission->submitted_at->toIso8601String(),
        ], 201);
    }
}
