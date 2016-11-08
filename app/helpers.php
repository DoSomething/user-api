<?php

use Carbon\Carbon;
use Northstar\Auth\Normalizer;

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
 * Return the Forge presenter.
 */
function forge()
{
    return new \Northstar\Http\Presenters\ForgePresenter();
}

/**
 * Make a Forge text field.
 *
 * @param $name
 * @param $label
 * @param null $value
 * @param null $placeholder
 * @return mixed
 */
function field($type, $name, $label, $value = null, $placeholder = null)
{
    return call_user_func_array([forge(), 'field'], func_get_args());
}
