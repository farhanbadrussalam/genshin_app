<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ResinAlert extends Model
{
    use HasFactory;

    protected $fillable = [
        'game_account_id',
        'resin_amount',
        'max_resin',
        'threshold',
        'alert_type',
        'message',
        'is_read',
        'notified_at',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'notified_at' => 'datetime',
    ];

    public function gameAccount()
    {
        return $this->belongsTo(GameAccount::class);
    }
}
