<?php

namespace App\Models;

use App\Models\Traits\HasTenantScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class FormSubmission extends Model
{
    use HasFactory, HasTenantScope;

    public $timestamps = false;

    protected $fillable = [
        'tenant_id',
        'form_id',
        'submission_uuid',
        'form_version',
        'payload',
        'ip_address',
        'user_agent',
        'submitted_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'form_version' => 'integer',
        'submitted_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($submission) {
            if (empty($submission->submission_uuid)) {
                $submission->submission_uuid = (string) Str::uuid();
            }
            if (empty($submission->submitted_at)) {
                $submission->submitted_at = now();
            }
        });
    }

    public function form()
    {
        return $this->belongsTo(Form::class);
    }
}
