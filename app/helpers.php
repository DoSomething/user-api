<?php

use App\Auth\Entities\ClientEntity;
use App\Auth\Normalizer;
use App\Auth\Repositories\AccessTokenRepository;
use App\Auth\Repositories\KeyRepository;
use App\Auth\Repositories\ScopeRepository;
use App\Models\Client;
use Carbon\Carbon;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;
use libphonenumber\PhoneNumber;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberUtil;
use SeatGeek\Sixpack\Session\Base as Sixpack;

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

    if (!method_exists($normalizer, $type)) {
        throw new InvalidArgumentException(
            'There isn\'t a `' .
                $type .
                '` method on the normalizer (' .
                Normalizer::class .
                ').',
        );
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

    if (!$currentRoute) {
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
    if (!empty($oauthClientId)) {
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
    $iso = (new League\ISO3166\ISO3166())->all();

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
 * Get the postal code from the `X-Fastly-Postal-Code` header.
 *
 * @return string|null
 */
function postal_code()
{
    $code = request()->header('X-Fastly-Postal-Code');

    return $code ? Str::upper($code) : null;
}

/**
 * Get the region code from the `X-Fastly-Region-Code` header.
 *
 * @return string|null
 */
function region_code()
{
    $code = request()->header('X-Fastly-Region-Code');

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
function is_dosomething_domain(string $url): bool
{
    $host = parse_url($url, PHP_URL_HOST);

    if (!$host) {
        return false;
    }

    // Reject any URLs that include HTTP basic authentication, as this
    // can be used for a "redirect hijack" attack, since some browsers
    // will improperly read a URL-like username as the URL:
    if (parse_url($url, PHP_URL_USER)) {
        return false;
    }

    // Reject a host that includes an escaped '.' or '@', which could be used to
    // fool our domain check below (e.g. 'cobalt.io\.dosomething.org'):
    if (str_contains($host, '\.') || str_contains($host, '\@')) {
        return false;
    }

    return (bool) preg_match('/(^|\.)dosomething\.org$/', $host);
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
    if (!app()->runningInConsole()) {
        throw new InvalidArgumentException(
            'Cannot use throttle() outside of console scripts.',
        );
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
    return new HtmlString(
        '<script type="text/javascript">window.' .
            $store .
            ' = ' .
            json_encode($json) .
            '</script>',
    );
}

/**
 * Get the env vars which are safe for client usage.
 *
 * @return array
 */
function get_client_environment_vars()
{
    return [
        'PHOENIX_URL' => config('services.phoenix.url'),
    ];
}

/**
 * Setup a Sixpack experiment.
 *
 * @param string $experiment
 * @param array $alternatives
 *
 * @return string
 */
function participate($experiment, $alternatives)
{
    if (!config('services.sixpack.enabled')) {
        return false;
    }

    return app(Sixpack::class)
        ->participate($experiment, $alternatives)
        ->getAlternative();
}

/**
 * Convert a Sixpack experiment.
 *
 * @param string $experiment
 *
 * @return SeatGeek\Sixpack\Response\Conversion
 */
function convert($experiment)
{
    if (!config('services.sixpack.enabled')) {
        return;
    }

    return app(Sixpack::class)->convert($experiment);
}

/**
 * Create a formatted key-value string, like for source_detail.
 *
 * @param object $object
 * @return string
 */
function stringify_object($object)
{
    // e.g. utm_source:test,utm_medium:internet,utm_campaign:uconn_lady_huskies
    return str_replace('=', ':', http_build_query($object, '', ','));
}

/**
 * Parse a phone number into a PhoneNumber object.
 *
 * @param string $string
 * @return PhoneNumber
 */
function parse_mobile($string): ?PhoneNumber
{
    try {
        $parser = PhoneNumberUtil::getInstance();
        $number = $parser->parse($string, 'US');

        return $number;
    } catch (\libphonenumber\NumberParseException $e) {
        return null;
    }
}

/**
 * Format the given PhoneNumber as a string.
 */
function format_mobile(PhoneNumber $number, $format): string
{
    $parser = PhoneNumberUtil::getInstance();

    return $parser->format($number, $format);
}

/**
 * Create a "personal" access token that Northstar can use
 * to make API calls to resource servers (like Rogue).
 *
 * @return string
 */
function machine_token(...$scopes)
{
    $client = new ClientEntity('northstar', config('app.name'), $scopes);
    $scopeEntities = app(ScopeRepository::class)->create(...$scopes);

    $accessToken = app(AccessTokenRepository::class)->getNewToken(
        $client,
        $scopeEntities,
    );
    $accessToken->setPrivateKey(app(KeyRepository::class)->getPrivateKey());
    $accessToken->setIdentifier(bin2hex(random_bytes(40)));

    // Since this token is only used for Northstar's own API requests, we'll give it a very short TTL:
    $accessToken->setExpiryDateTime(
        (new \DateTimeImmutable())->add(new DateInterval('PT5M')),
    );

    return 'Bearer ' . (string) $accessToken;
}

/**
 * Check if the given string is a valid Mongo ObjectID.
 *
 * @param  string
 * @return bool
 */
function is_valid_objectid(string $string): bool
{
    return (bool) preg_match('/^[a-f\d]{24}$/i', $string);
}

/**
 * Create a "revealer" toggle for sensitive fields.
 */
function revealer(...$fields)
{
    $currentIncludes = csv_query('include');

    $isActive = count(array_intersect($currentIncludes, $fields)) > 0;

    $newFields = $isActive
        ? array_diff($currentIncludes, $fields)
        : array_merge($currentIncludes, $fields);

    $linkTag =
        '<a href="' .
        e(request()->url() . '?include=' . implode(',', $newFields)) .
        '" class="reveal ' .
        ($isActive ? 'is-active' : '') .
        '" data-turbolinks-action="replace" data-turbolinks-scroll="false"><span>reveal</span></a>';

    return new HtmlString($linkTag);
}

/**
 * Read a given CSV-formatted query string.
 *
 * @param string $key
 * @param string[] $default
 * @return string[]
 */
function csv_query(string $key, array $default = []): array
{
    $query = request()->query($key);

    if (!$query) {
        return $default;
    }

    return explode(',', $query);
}

/**
 * Print user-friendly name from an ISO country code.
 *
 * @param  string $code
 * @return string
 */
function country_name($code)
{
    $isoCodes = new \Sokil\IsoCodes\IsoCodesFactory();
    $country = $isoCodes->getCountries()->getByAlpha2($code);

    return $country ? $country->getName() : 'Unknown (' . $code . ')';
}
