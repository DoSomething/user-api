<?php

namespace Northstar\Http\Middleware;

use Closure;
use App;
use Illuminate\Support\Facades\Request;

class Localization
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
        $countryCode = Request::server('HTTP_X_FASTLY_COUNTRY_CODE');
        if ($countryCode) {
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
        }

        return $next($request);
    }
}
