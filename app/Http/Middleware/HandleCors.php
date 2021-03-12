<?php

namespace App\Http\Middleware;

use Closure;
use Fruitcake\Cors\HandleCors as BaseMiddleware;

class HandleCors extends BaseMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure $next
     */
    public function handle($request, Closure $next)
    {


        return parent::handle($request, $next);
    }
}
