<?php

namespace App\Http\Middleware;

use App;
use Closure;

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
        $countryCode = country_code();

        switch ($countryCode) {
            case 'US':
                App::setLocale('en');
                break;
            case 'MX':
                App::setLocale('es-mx');
                break;
            default:
                App::setLocale('en');
                break;
        }

        return $next($request);
    }
}
