<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DailyCheckinLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'game_account_id',
        'status',
        'reward_name',
        'reward_amount',
        'reward_icon',
        'message',
        'is_auto',
        'checked_at',
    ];

    protected $casts = [
        'is_auto' => 'boolean',
        'checked_at' => 'datetime',
    ];

    public function gameAccount()
    {
        return $this->belongsTo(GameAccount::class);
    }
}
