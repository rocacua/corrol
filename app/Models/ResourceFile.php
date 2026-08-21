<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class ResourceFile extends Model
{
    use HasFactory;

    protected $fillable = [
        'file_path_or_url',
        'is_external',
        'file_type',
        'mime_type',
        'size_in_bytes',
        'metadata',
    ];

    protected $casts = [
        'is_external' => 'boolean',
        'metadata' => 'array',
    ];

    public function resource(): MorphOne
    {
        return $this->morphOne(Resource::class, 'resourceable');
    }
}