<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FormVersion extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'form_id',
        'version_number',
        'schema',
        'change_summary',
        'created_by',
        'created_at',
    ];

    protected $casts = [
        'version_number' => 'integer',
        'schema' => 'array',
        'created_at' => 'datetime',
    ];

    public function form()
    {
        return $this->belongsTo(Form::class);
    }
}
