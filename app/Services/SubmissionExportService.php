<?php

namespace App\Services;

use App\Models\Form;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SubmissionExportService
{
    /**
     * Streams a CSV download for a given form's submissions.
     */
    public function exportCsv(Form $form): StreamedResponse
    {
        $fileName = 'submissions_' . $form->public_slug . '_' . date('Y-m-d_His') . '.csv';

        $schema = $form->schema;
        $fieldKeys = [];
        $headers = ['Submission ID', 'Submitted At', 'IP Address'];

        if (!empty($schema['sections'])) {
            foreach ($schema['sections'] as $section) {
                if (!empty($section['fields'])) {
                    foreach ($section['fields'] as $field) {
                        if (!empty($field['key'])) {
                            $fieldKeys[] = $field['key'];
                            $headers[] = $field['label'] ?? $field['key'];
                        }
                    }
                }
            }
        }

        return response()->streamDownload(function () use ($form, $fieldKeys, $headers) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $headers);

            $submissions = $form->submissions()->latest('submitted_at')->cursor();

            foreach ($submissions as $sub) {
                $payload = $sub->payload ?? [];
                $row = [
                    $sub->submission_uuid,
                    $sub->submitted_at ? $sub->submitted_at->toDateTimeString() : '',
                    $sub->ip_address,
                ];

                foreach ($fieldKeys as $key) {
                    $val = $payload[$key] ?? '';
                    if (is_array($val)) {
                        if (!empty($val['url'])) {
                            // Output full URL path for document upload fields
                            $val = $val['url'];
                        } elseif (!empty($val['path'])) {
                            $val = asset('storage/' . $val['path']);
                        } else {
                            // Multiple choice / checkbox array values
                            $val = implode(', ', $val);
                        }
                    }
                    $row[] = (string) $val;
                }

                fputcsv($handle, $row);
            }

            fclose($handle);
        }, $fileName, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ]);
    }
}
