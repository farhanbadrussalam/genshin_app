<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryMaterial extends Model
{
    use HasFactory;

    protected $fillable = [
        'game_account_id',
        'material_id',
        'amount',
    ];

    public function gameAccount()
    {
        return $this->belongsTo(GameAccount::class);
    }

    public function material()
    {
        return $this->belongsTo(Material::class);
    }
}
