<?php

use Carbon\Carbon;
use Northstar\Auth\Normalizer;
use Northstar\Models\Client;

/**
 * Normalize the given value.
 *
 * @param string $type - The field to normalize
 * @param mixed $value - The value to be normalized
 * @return Normalizer|mixed
 */
function normalize($type = null, $value = null)
{
    $normalizer = app(Normalizer::class);

    // If no arguments given, return the normalizer instance.
    if (is_null($type)) {
        return $normalizer;
    }

    if (! method_exists($normalizer, $type)) {
        throw new InvalidArgumentException('There isn\'t a `'.$type.'` method on the normalizer ('.Normalizer::class.').');
    }

    // Otherwise, send the given value to the corresponding method.
    return $normalizer->{$type}($value);
}

/**
 * Format a Carbon date if available to a specified format.
 *
 * @param  string $date
 * @param string $format
 * @return null|string
 */
function format_date($date, $format = 'M j, Y')
{
    if (is_null($date)) {
        return null;
    }

    try {
        $date = new Carbon($date);
    } catch (InvalidArgumentException $e) {
        return null;
    }

    return $date->format($format);
}

/**
 * Check if the current route has any middleware attached.
 *
 * @param  null|string  $middleware
 * @return bool
 */
function has_middleware($middleware = null)
{
    $currentRoute = app('router')->getCurrentRoute();

    if (! $currentRoute) {
        return false;
    }

    if ($middleware) {
        return in_array($middleware, $currentRoute->middleware());
    }

    return $currentRoute->middleware() ? true : false;
}

/**
 * Get the name of the client executing the current request.
 *
 * @return string
 */
function client_id()
{
    $clientId = auth()->guard('api')->client();

    if (! empty($clientId)) {
        return $clientId;
    }

    // If not an API request, use Client ID from `/authorize` call or just 'northstar'.
    return session('authorize_client_id', 'northstar');
}
