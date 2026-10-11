<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        'calculator/*',
        'inventory/materials/*',
        'hoyolab/*',
        'inventory/characters/sync-*',
        'inventory/weapons/sync-*',
        'inventory/artifacts/sync-*',
        'inventory/sync-all',
        'character/sync-all',
        'weapon/sync-all',
        'artifact-scoring/*',
        'enemy/sync-all',
        'material/sync-all',
        'family/sync-all',
        'task/toggle-subtask/*',
        'party/*',
    ];
}
