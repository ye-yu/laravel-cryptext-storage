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
        'api/v1/login',
        'api/v1/logout',
        'api/v1/verify',
        'api/v1/notes',
        'api/v1/keys',
        'api/v1/notes/*',
        'api/v1/keys/*',
    ];
}
