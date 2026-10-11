<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class task extends Model
{
    use HasFactory;

    protected $fillable = [
        'game_account_id',
        'nama_task',
        'images',
        'jenis',
        'status',
        'prioritas'
    ];

    public function gameAccount(): BelongsTo
    {
        return $this->belongsTo(GameAccount::class, 'game_account_id');
    }

    public function sub_task(): HasMany
    {
        return $this->HasMany(subTask::class, 'task_id', 'id')->with('material');
    }
}
