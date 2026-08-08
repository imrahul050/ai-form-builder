<?php

namespace App\Http\Controllers;

use App\Models\Form;
use App\Models\FormSubmission;
use App\Models\FormVersion;
use App\Models\Tenant;
use App\Services\SubmissionExportService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class FormBuilderController extends Controller
{
    protected function ensureTenantContext()
    {
        if (!session()->has('tenant_id')) {
            $tenant = Tenant::firstOrCreate(
                ['slug' => 'default-tenant'],
                ['name' => 'Default Organization', 'uuid' => (string) Str::uuid()]
            );
            session(['tenant_id' => $tenant->id]);
        }
    }

    public function index()
    {
        $this->ensureTenantContext();
        $forms = Form::latest()->paginate(10);
        return view('forms.index', compact('forms'));
    }

    public function create()
    {
        $this->ensureTenantContext();
        return view('forms.builder');
    }

    public function edit(Form $form)
    {
        $this->ensureTenantContext();
        return view('forms.builder', compact('form'));
    }

    public function showSubmissions(Form $form, Request $request)
    {
        $this->ensureTenantContext();
        $query = $form->submissions()->latest('submitted_at');

        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where('payload', 'like', "%{$search}%");
        }

        $submissions = $query->paginate(15);
        return view('forms.submissions', compact('form', 'submissions'));
    }

    public function exportCsv(Form $form, SubmissionExportService $exportService)
    {
        $this->ensureTenantContext();
        return $exportService->exportCsv($form);
    }

    public function showVersions(Form $form)
    {
        $this->ensureTenantContext();
        $versions = $form->versions()->orderBy('version_number', 'desc')->get();
        return view('forms.versions', compact('form', 'versions'));
    }

    public function rollbackVersion(Form $form, FormVersion $version)
    {
        $this->ensureTenantContext();
        if ($version->form_id !== $form->id) {
            abort(403);
        }

        $form->update([
            'schema' => $version->schema,
            'current_version' => $form->current_version + 1,
        ]);

        FormVersion::create([
            'form_id' => $form->id,
            'version_number' => $form->current_version,
            'schema' => $version->schema,
            'change_summary' => "Rollback to Version {$version->version_number}",
        ]);

        return redirect()->route('forms.edit', $form)->with('status', "Successfully rolled back to Version {$version->version_number}");
    }

    public function showAnalytics(Form $form)
    {
        $this->ensureTenantContext();
        $totalViews = $form->analyticsEvents()->where('event_type', 'view')->count();
        $totalSubmits = $form->analyticsEvents()->where('event_type', 'submit')->count();
        $totalAbandons = $form->analyticsEvents()->where('event_type', 'abandon')->count();

        $completionRate = $totalViews > 0 ? round(($totalSubmits / $totalViews) * 100, 1) : 0;

        return view('forms.analytics', compact('form', 'totalViews', 'totalSubmits', 'totalAbandons', 'completionRate'));
    }
}
