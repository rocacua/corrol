<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Resource extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'privacy',
        'type',
        'game',
        'campaign',
        'author',
        'tags',
        'resourceable_id',
        'resourceable_type',
    ];

    protected $casts = [
        'tags' => 'array',
    ];

    /**
     * Relación polimórfica para obtener el detalle del recurso (File, Map, Sheet...)
     */
    public function resourceable(): MorphTo
    {
        return $this->morphTo();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}