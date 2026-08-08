<?php

namespace App\Models;

use App\Models\Traits\HasTenantScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AnalyticsEvent extends Model
{
    use HasFactory, HasTenantScope;

    public $timestamps = false;

    protected $fillable = [
        'tenant_id',
        'form_id',
        'session_id',
        'event_type',
        'field_key',
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
