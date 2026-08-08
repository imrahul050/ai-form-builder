<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('form_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_id')->constrained('forms')->onDelete('cascade');
            $table->unsignedInteger('version_number');
            $table->json('schema');
            $table->string('change_summary', 500)->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['form_id', 'version_number'], 'uk_form_version');
            $table->index('form_id', 'idx_versions_form_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('form_versions');
    }
};
