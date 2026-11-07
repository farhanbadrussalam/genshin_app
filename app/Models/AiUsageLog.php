<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiUsageLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'feature',
        'model_used',
        'prompt_tokens',
        'candidates_tokens',
        'total_tokens',
        'status',
        'error_message',
        'duration_ms',
        'created_at',
    ];

    protected $casts = [
        'prompt_tokens'     => 'integer',
        'candidates_tokens' => 'integer',
        'total_tokens'      => 'integer',
        'duration_ms'       => 'integer',
        'created_at'        => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

