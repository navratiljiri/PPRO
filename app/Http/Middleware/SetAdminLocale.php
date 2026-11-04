<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Forces the Czech locale for the Filament administration panel.
 *
 * The administration UI is Czech-only, so it must not depend on APP_LOCALE
 * in a local .env file (built-in Filament strings, validation messages and dates).
 */
class SetAdminLocale
{
    public const LOCALE = 'cs';

    public function handle(Request $request, Closure $next): Response
    {
        app()->setLocale(self::LOCALE);

        return $next($request);
    }
}
