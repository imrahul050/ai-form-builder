<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_generation_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
            $table->foreignId('form_id')->nullable()->constrained('forms')->onDelete('set null');
            $table->string('job_id')->index();
            $table->text('prompt');
            $table->enum('mode', ['create', 'edit'])->default('create');
            $table->string('model');
            $table->longText('raw_response')->nullable();
            $table->unsignedInteger('token_count')->nullable();
            $table->unsignedInteger('latency_ms')->nullable();
            $table->enum('status', ['pending', 'completed', 'failed'])->default('pending');
            $table->text('error_log')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['tenant_id', 'status'], 'idx_ai_logs_tenant_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_generation_logs');
    }
};
