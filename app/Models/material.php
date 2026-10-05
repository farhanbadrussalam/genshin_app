<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class material extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'familie_id',
        'rarity',
        'category',
        'materialtype',
        'dropdomain',
        'amount',
        'description',
        'images',
        'daysofweek',
        'source',
    ];

    public function family()
    {
        return $this->belongsTo(family::class, 'familie_id');
    }

    public function inventoryMaterials()
    {
        return $this->hasMany(InventoryMaterial::class, 'material_id');
    }
}
