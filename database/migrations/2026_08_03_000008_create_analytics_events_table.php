<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('analytics_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
            $table->foreignId('form_id')->constrained('forms')->onDelete('cascade');
            $table->string('session_id', 100)->index();
            $table->enum('event_type', ['view', 'field_focus', 'section_next', 'submit', 'abandon']);
            $table->string('field_key', 100)->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['form_id', 'event_type'], 'idx_analytics_form_event');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('analytics_events');
    }
};
