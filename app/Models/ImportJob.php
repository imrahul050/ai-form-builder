<?php

namespace App\Models;

use App\Models\Traits\HasTenantScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ImportJob extends Model
{
    use HasFactory, HasTenantScope;

    protected $fillable = [
        'tenant_id',
        'file_name',
        'file_path',
        'file_type',
        'status',
        'extracted_structure',
        'mapping_schema',
        'error_message',
    ];

    protected $casts = [
        'extracted_structure' => 'array',
        'mapping_schema' => 'array',
    ];
}
