<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('form_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
            $table->foreignId('form_id')->constrained('forms')->onDelete('cascade');
            $table->uuid('submission_uuid')->unique();
            $table->unsignedInteger('form_version')->default(1);
            $table->json('payload');
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('submitted_at')->useCurrent();

            $table->index(['form_id', 'submitted_at'], 'idx_submissions_form_date');
            $table->index('tenant_id', 'idx_submissions_tenant');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('form_submissions');
    }
};
