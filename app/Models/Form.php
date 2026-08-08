<?php

namespace App\Models;

use App\Models\Traits\HasTenantScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Form extends Model
{
    use HasFactory, HasTenantScope;

    protected $fillable = [
        'tenant_id',
        'uuid',
        'title',
        'description',
        'public_slug',
        'is_active',
        'current_version',
        'schema',
        'created_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'current_version' => 'integer',
        'schema' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($form) {
            if (empty($form->uuid)) {
                $form->uuid = (string) Str::uuid();
            }
            if (empty($form->public_slug)) {
                $form->public_slug = Str::slug($form->title) . '-' . Str::random(6);
            }
        });
    }

    public function versions()
    {
        return $this->hasMany(FormVersion::class);
    }

    public function submissions()
    {
        return $this->hasMany(FormSubmission::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function analyticsEvents()
    {
        return $this->hasMany(AnalyticsEvent::class);
    }
}
