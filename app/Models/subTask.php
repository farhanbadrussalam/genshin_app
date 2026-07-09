<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\hasOne;

class subTask extends Model
{
    use HasFactory;

    protected $fillable = [
        'task_id',
        'material_id',
        'amount'
    ];

    public function material(): hasOne
    {
        return $this->hasOne(material::class, 'id', 'material_id');
    }
}
