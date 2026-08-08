<?php

namespace App\Models;

use App\Models\Traits\HasTenantScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AiGenerationLog extends Model
{
    use HasFactory, HasTenantScope;

    public $timestamps = false;

    protected $fillable = [
        'tenant_id',
        'form_id',
        'job_id',
        'prompt',
        'mode',
        'model',
        'raw_response',
        'token_count',
        'latency_ms',
        'status',
        'error_log',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function form()
    {
        return $this->belongsTo(Form::class);
    }
}
