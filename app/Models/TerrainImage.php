<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TerrainImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'terrain_id',
        'image_path',
        'uploaded_at',
    ];

    protected $casts = [
        'uploaded_at' => 'datetime',
    ];

    public $timestamps = false;

    public function terrain(): BelongsTo
    {
        return $this->belongsTo(Terrain::class);
    }
}
