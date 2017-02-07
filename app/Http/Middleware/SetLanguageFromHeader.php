<?php

namespace Northstar\Http\Middleware;

use Closure;
use App;

class SetLanguageFromHeader
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $countryCode = strtolower($request->header('X-Fastly-Country-Code'));

        switch ($countryCode) {
            case 'us':
                App::setLocale('en');
                break;
            case 'mx':
                App::setLocale('es-mx');
                break;
            default:
                App::setLocale('en');
                break;
        }

        return $next($request);
    }
}
