<?php

namespace Northstar\Http\Middleware;

use Closure;
use App;
use Illuminate\Support\Facades\Request;

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

        if ($countryCode) {
            switch ($countryCode) {
                case 'us':
                    $language = 'en';
                    break;
                case 'mx':
                    $language = 'es-mx';
                    break;
                default:
                    $language = 'en';
                    break;
            }
        }

        App::setLocale($language);
        app('JavaScript')->put([
            'language' => $language,
        ]);

        return $next($request);
    }
}
