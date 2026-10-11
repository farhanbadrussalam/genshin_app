<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
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

    protected static function booted()
    {
        // Global scope: hanya tampilkan task milik akun game user yang sedang login
        static::addGlobalScope('user_ownership', function (Builder $builder) {
            if (auth()->check()) {
                $builder->whereHas('gameAccount', function ($query) {
                    $query->where('user_id', auth()->id());
                });
            }
        });
    }

    public function gameAccount(): BelongsTo
    {
        return $this->belongsTo(GameAccount::class, 'game_account_id');
    }

    public function sub_task(): HasMany
    {
        return $this->hasMany(subTask::class, 'task_id', 'id')->with('material');
    }
}
