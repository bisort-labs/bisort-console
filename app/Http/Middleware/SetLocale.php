<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Support\Localization;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $supportedLocales = Localization::supportedLocales();
        $defaultLocale = Localization::defaultLocale();
        $sessionLocale = $request->session()->get('locale');
        $locale = is_string($sessionLocale) && in_array($sessionLocale, $supportedLocales, true)
            ? $sessionLocale
            : $defaultLocale;

        app()->setLocale($locale);

        return $next($request);
    }
}
