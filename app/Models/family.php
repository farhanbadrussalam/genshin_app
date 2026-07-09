<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class family extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    public function material(): HasMany
    {
        return $this->HasMany(material::class, 'familie_id', 'id')->orderBy('rarity','ASC');
    }
}
