<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Weapon extends Model
{
    use HasFactory;

    protected $table = 'weapons';

    protected $fillable = [
        'game_id',
        'name',
        'slug',
        'type',
        'rarity',
        'base_atk',
        'sub_stat_type',
        'sub_stat_value',
        'passive_name',
        'passive_desc',
        'icon_url',
    ];

    protected $casts = [
        'game_id'  => 'integer',
        'rarity'   => 'integer',
        'base_atk' => 'integer',
    ];

    /**
     * Relasi ke inventori senjata akun pemain
     */
    public function inventoryWeapons(): HasMany
    {
        return $this->hasMany(InventoryWeapon::class, 'weapon_id');
    }

    /**
     * Scope filter pencarian dan tipe
     */
    public function scopeFilter(Builder $query, array $filters): Builder
    {
        return $query
            ->when($filters['search'] ?? null, function ($q, $search) {
                $q->where('name', 'like', "%{$search}%");
            })
            ->when($filters['type'] ?? null, function ($q, $type) {
                if ($type !== 'all') {
                    $q->where('type', $type);
                }
            })
            ->when($filters['rarity'] ?? null, function ($q, $rarity) {
                if ($rarity !== 'all') {
                    $q->where('rarity', (int)$rarity);
                }
            });
    }
}
