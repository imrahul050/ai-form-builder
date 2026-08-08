<?php

namespace Tests\Feature;

use App\Models\Form;
use App\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class FormBuilderTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_render_dashboard(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('Forms Dashboard');
    }

    public function test_can_fetch_public_form_schema_via_api(): void
    {
        $tenant = Tenant::create(['uuid' => (string) Str::uuid(), 'name' => 'Test Tenant', 'slug' => 'test-tenant']);
        $form = Form::create([
            'tenant_id' => $tenant->id,
            'title' => 'Public API Form',
            'public_slug' => 'public-api-form',
            'schema' => [
                'title' => 'Public API Form',
                'sections' => []
            ]
        ]);

        $response = $this->getJson("/api/v1/forms/{$form->public_slug}");
        $response->assertStatus(200);
        $response->assertJsonPath('title', 'Public API Form');
    }

    public function test_can_load_demo_excel_import(): void
    {
        $response = $this->post('/import/demo-excel');
        $response->assertStatus(302);
    }

    public function test_can_download_sample_excel_template(): void
    {
        $response = $this->get('/import/download-sample-excel');
        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }
}
