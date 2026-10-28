<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
     
    public function handle(Request $request, Closure $next): Response
    {
        $supported = array_keys(config('locales.supported', []));
        $locale = $request->session()->get('locale');

        if (! is_string($locale) || ! in_array($locale, $supported, true)) {
            $locale = config('locales.default', config('app.locale', 'id'));
        }

        if (in_array($locale, $supported, true)) {
            app()->setLocale($locale);
        }

        return $next($request);
    }
}

