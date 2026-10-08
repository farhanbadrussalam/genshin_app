<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EnemyDrop extends Model
{
    use HasFactory;

    protected $table = 'enemy_drops';

    protected $fillable = [
        'enemy_id',
        'material_id',
        'name',
        'drop_type',
        'rarity',
        'minimum_level',
        'source_note',
        'icon_url',
    ];

    protected $casts = [
        'rarity' => 'integer',
        'minimum_level' => 'integer',
    ];

    public function enemy(): BelongsTo
    {
        return $this->belongsTo(Enemy::class, 'enemy_id');
    }

    public function material(): BelongsTo
    {
        return $this->belongsTo(material::class, 'material_id');
    }

    /**
     * Dapatkan warna background/border berdasarkan rarity
     */
    public function getRarityColorAttribute(): string
    {
        return match ($this->rarity) {
            5 => '#e09c3a', // Emas
            4 => '#a855f7', // Ungu
            3 => '#3b82f6', // Biru
            2 => '#22c55e', // Hijau
            default => '#94a3b8', // Abu-abu
        };
    }
}