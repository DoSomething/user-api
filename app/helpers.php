<?php

use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\HtmlString;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberUtil;
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
 * @param Carbon|string $date
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
 * Format a date as an ISO-8601 timestamp.
 *
 * @param Carbon|string $date
 * @return null|string
 */
function iso8601($date)
{
    // Fun fact: PHP's built-in DateTime::ISO8601 constant is wrong,
    // so that's why we use Carbon::ATOM here. (https://goo.gl/MzIaqP)
    return format_date($date, Carbon::ATOM);
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
    $oauthClientId = request()->attributes->get('oauth_client_id');
    if (! empty($oauthClientId)) {
        return $oauthClientId;
    }

    // Otherwise, try to get the client from the legacy X-DS-REST-API-Key header.
    $client_secret = request()->header('X-DS-REST-API-Key');
    $client = Client::where('client_secret', $client_secret)->first();
    if ($client) {
        return $client->client_id;
    }

    // If not an API request, use Client ID from `/authorize` call or just 'northstar'.
    return session('authorize_client_id', 'northstar');
}

/**
 * Get a list of countries keyed by ISO country code.
 *
 * @return \Illuminate\Support\Collection
 */
function get_countries()
{
    $iso = (new League\ISO3166\ISO3166)->getAll();

    return collect($iso)->pluck('name', 'alpha2');
}

/**
 * Get the country code from the `X-Fastly-Country-Code` header.
 *
 * @return string|null
 */
function country_code()
{
    $code = request()->header('X-Fastly-Country-Code');

    return $code ? Str::upper($code) : null;
}

/**
 * Replace the given keys with a value.
 *
 * @param $array
 * @param $keys
 * @return mixed
 */
function array_replace_keys($array, $keys, $value)
{
    foreach ($keys as $key) {
        if (isset($array[$key])) {
            $array[$key] = $value;
        }
    }

    return $array;
}

/**
 * Format the given Birthday string, and check if its
 * null or partial birthday first. Returns a date
 * suitable for a Northstar profile or null.
 *
 * @param  string $birthday
 * @return date|null
 */
function format_birthdate($birthdate)
{
    if (is_null($birthdate) || empty($birthdate)) {
        return null;
    }

    if (count(explode('/', $birthdate)) <= 2) {
        return null;
    }

    return format_date($birthdate, 'Y-m-d');
}

/**
 * Format a legacy phone number to a proper number format.
 *
 * @param  string $mobile
 * @return string
 */
function format_legacy_mobile($mobile)
{
    try {
        $parser = PhoneNumberUtil::getInstance();
        $number = $parser->parse($mobile, 'US');
        $formatted = $parser->format($number, PhoneNumberFormat::NATIONAL);

        return preg_replace('#[^0-9]+#', '', $formatted);
    } catch (\libphonenumber\NumberParseException $e) {
        return null;
    }
}

/**
 * Check if the given url is a *.dosomething.org domain.
 *
 * @param  string  $url
 * @return bool
 */
function is_dosomething_domain($url)
{
    $parsed = parse_url($url);

    if (! array_key_exists('host', $parsed)) {
        return false;
    }

    return ends_with($parsed['host'], 'dosomething.org') !== false;
}

/**
 * Throttle a script by setting a limit on the number of
 * times something can happen per minute.
 *
 * @param int $throughput
 * @return void
 */
function throttle($throughput)
{
    // Refuse to throttle non-console contexts.
    if (! app()->runningInConsole()) {
        throw new InvalidArgumentException('Cannot use throttle() outside of console scripts.');
    }

    if (empty($throughput)) {
        return;
    }

    $seconds = 60 / $throughput;
    usleep($seconds * 1000000);
}

/**
 * Create a script tag to set a global variable.
 *
 * @param $json
 * @param string $store
 * @return HtmlString
 */
function scriptify($json = [], $store = 'STATE')
{
    return new HtmlString('<script type="text/javascript">window.'.$store.' = '.json_encode($json).'</script>');
}
