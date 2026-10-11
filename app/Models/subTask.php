<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class subTask extends Model
{
    use HasFactory;

    protected $fillable = [
        'task_id',
        'material_id',
        'amount',
        'is_completed',
    ];

    protected $casts = [
        'is_completed' => 'boolean',
        'amount'       => 'integer',
    ];

    public function material(): HasOne
    {
        return $this->hasOne(material::class, 'id', 'material_id');
    }
}