<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class ResourceMap extends Model
{
    use HasFactory;

    protected $fillable = [
        'map_image_url',
        'markers',
    ];

    protected $casts = [
        'markers' => 'array',
    ];

    public function resource(): MorphOne
    {
        return $this->morphOne(Resource::class, 'resourceable');
    }
}