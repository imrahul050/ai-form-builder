<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('import_jobs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
            $table->string('file_name');
            $table->string('file_path', 500);
            $table->enum('file_type', ['docx', 'xlsx']);
            $table->enum('status', ['uploaded', 'parsing', 'preview_ready', 'committed', 'failed'])->default('uploaded');
            $table->json('extracted_structure')->nullable();
            $table->json('mapping_schema')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('import_jobs');
    }
};
