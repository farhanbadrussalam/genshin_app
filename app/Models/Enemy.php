<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Enemy extends Model
{
    use HasFactory;

    protected $table = 'enemies';

    protected $fillable = [
        'name',
        'slug',
        'category',
        'family',
        'region',
        'elements',
        'description',
        'icon_url',
        'mora_gained',
        'immunities',
        'elemental_res',
        'weakness_elements',
        'recommended_mechanics',
        'tips_strategy',
        'is_active',
    ];

    protected $casts = [
        'elements' => 'array',
        'immunities' => 'array',
        'elemental_res' => 'array',
        'weakness_elements' => 'array',
        'recommended_mechanics' => 'array',
        'is_active' => 'boolean',
        'mora_gained' => 'integer',
    ];

    public function drops(): HasMany
    {
        return $this->hasMany(EnemyDrop::class, 'enemy_id')->orderByDesc('rarity');
    }

    /**
     * Mutator agar kolom elements selalu berupa 1D flat array string
     */
    public function setElementsAttribute($value): void
    {
        $this->attributes['elements'] = json_encode($this->flattenArray($value));
    }

    /**
     * Accessor elemen yang selalu bersih (1D array string)
     */
    public function getCleanElementsAttribute(): array
    {
        return $this->flattenArray($this->elements);
    }

    /**
     * Dapatkan elemen utama musuh
     */
    public function getFirstElementAttribute(): string
    {
        $flat = $this->flattenArray($this->elements);
        if (!empty($flat)) {
            return (string)$flat[0];
        }
        return 'Neutral';
    }

    /**
     * Helper meratakan array bertingkat menjadi 1D array string
     */
    protected function flattenArray($items): array
    {
        if (empty($items)) {
            return [];
        }

        if (is_string($items)) {
            return [trim($items)];
        }

        $result = [];
        array_walk_recursive($items, function ($item) use (&$result) {
            if (is_string($item) && trim($item) !== '') {
                $result[] = trim($item);
            }
        });

        return array_values(array_unique($result));
    }

    /**
     * Map elemen ke warna visual hex
     */
    public function getElementColorAttribute(): string
    {
        return match (strtolower($this->first_element)) {
            'pyro' => '#ef4444',
            'hydro' => '#06b6d4',
            'anemo' => '#10b981',
            'electro' => '#a855f7',
            'dendro' => '#84cc16',
            'cryo' => '#38bdf8',
            'geo' => '#eab308',
            default => '#94a3b8',
        };
    }

    /**
     * Class badge kategori musuh
     */
    public function getCategoryBadgeClassAttribute(): string
    {
        return match ($this->category) {
            'Weekly Bosses' => 'badge-weekly-boss',
            'Normal Bosses' => 'badge-normal-boss',
            'Elite Enemies' => 'badge-elite-enemy',
            default => 'badge-common-enemy',
        };
    }
}