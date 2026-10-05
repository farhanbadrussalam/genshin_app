<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ArtifactSet extends Model
{
    use HasFactory;

    protected $table = 'artifact_sets';

    protected $fillable = [
        'set_id',
        'name',
        'slug',
        'max_rarity',
        'two_piece_bonus',
        'four_piece_bonus',
        'icon_url',
    ];

    protected $casts = [
        'set_id'     => 'integer',
        'max_rarity' => 'integer',
    ];

    /**
     * Relasi ke inventori artifact pemain
     */
    public function inventoryArtifacts(): HasMany
    {
        return $this->hasMany(InventoryArtifact::class, 'artifact_set_id');
    }

    /**
     * Scope filter pencarian dan rarity
     */
    public function scopeFilter(Builder $query, array $filters): Builder
    {
        return $query
            ->when($filters['search'] ?? null, function ($q, $search) {
                $q->where('name', 'like', "%{$search}%");
            })
            ->when($filters['rarity'] ?? null, function ($q, $rarity) {
                if ($rarity !== 'all') {
                    $q->where('max_rarity', (int)$rarity);
                }
            });
    }
}
