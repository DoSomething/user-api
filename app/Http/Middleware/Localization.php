<?php

namespace Northstar\Http\Middleware;

use Closure;
use App;
use Illuminate\Support\Facades\Request;

class Localization
{

    /**
     * Map the given two letter ISO country code to
     * one of our supported languages or the fallback language.
     *
     * @param  String $countryCode
     * @return String
     */
    public function mapCountryToLanguage($countryCode) {
        $countryCode = strtolower($countryCode);


    }

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
