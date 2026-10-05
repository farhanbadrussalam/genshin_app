<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Character extends Model
{
    use HasFactory;

    protected $table = 'characters';

    protected $fillable = [
        'avatar_id',
        'name',
        'slug',
        'element',
        'weapon_type',
        'rarity',
        'region',
        'icon_url',
        'splash_url',
        'description',
        'is_active',
    ];

    protected $casts = [
        'avatar_id' => 'integer',
        'rarity' => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * Map elemen ke warna visual hex
     */
    public function getElementColorAttribute(): string
    {
        return match (strtolower($this->element)) {
            'pyro' => '#ef4444',
            'hydro' => '#3b82f6',
            'anemo' => '#10b981',
            'electro' => '#a855f7',
            'dendro' => '#22c55e',
            'cryo' => '#06b6d4',
            'geo' => '#eab308',
            default => '#94a3b8',
        };
    }

    /**
     * Scope filter pencarian dan opsi filter
     */
    public function scopeFilter(Builder $query, array $filters): Builder
    {
        return $query
            ->when($filters['search'] ?? null, function ($q, $search) {
                $q->where('name', 'like', "%{$search}%");
            })
            ->when($filters['element'] ?? null, function ($q, $element) {
                if ($element !== 'all') {
                    $q->where('element', $element);
                }
            })
            ->when($filters['rarity'] ?? null, function ($q, $rarity) {
                if ($rarity !== 'all') {
                    $q->where('rarity', (int)$rarity);
                }
            })
            ->when($filters['weapon'] ?? null, function ($q, $weapon) {
                if ($weapon !== 'all') {
                    $q->where('weapon_type', $weapon);
                }
            })
            ->when($filters['region'] ?? null, function ($q, $region) {
                if ($region !== 'all') {
                    $q->where('region', $region);
                }
            });
    }

    /**
     * Relasi ke entri inventori karakter
     */
    public function inventoryCharacters()
    {
        return $this->hasMany(InventoryCharacter::class, 'character_id');
    }

    /**
     * Senjata yang sedang dipakai oleh karakter ini
     */
    public function equippedWeapons()
    {
        return $this->hasMany(InventoryWeapon::class, 'equipped_character_id');
    }

    /**
     * Artifact yang sedang dipakai oleh karakter ini
     */
    public function equippedArtifacts()
    {
        return $this->hasMany(InventoryArtifact::class, 'equipped_character_id');
    }

    /**
     * Aturan scoring artifact untuk karakter ini
     */
    public function scoringRule()
    {
        return $this->hasOne(ArtifactScoringRule::class, 'character_id');
    }
}
