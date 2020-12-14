<?php

namespace App\Services\Resources;

use Carbon\Carbon;
use DoSomething\Gateway\Common\ApiResponse;
use Illuminate\Support\Arr;
use JsonSerializable;

class Redirect extends ApiResponse implements JsonSerializable
{
    /**
     * Create a new redirect from the given API response.
     * @param $attributes
     */
    public function __construct($attributes)
    {
        if ($attributes instanceof self) {
            $attributes = $attributes->attributes;
        }

        parent::__construct($attributes);
    }

    /**
     * Create a new redirect from the Fastly dictionary item.
     * @param $redirect
     */
    public static function fromItems($redirect)
    {
        return new static([
            'id' => self::encodeId($redirect),
            'path' => $redirect['item_key'],
            'target' => $redirect['item_value'],
            'updated_at' => Arr::get($redirect, 'updated_at', Carbon::now()), // not returned from updates.
            'created_at' => Arr::get($redirect, 'updated_at', Carbon::now()), // not returned from updates.
        ]);
    }

    /**
     * Encode the redirect key to be URL-safe.
     *
     * @param string $redirect
     * @return string
     */
    public static function encodeId($redirect)
    {
        // In order to use these in URLs (like /redirects/:id), we need to
        // make them URL-safe. We first base64-encode the path, and then
        // replace any slashes with underscores.
        return str_replace('/', '_', base64_encode($redirect['item_key']));
    }

    /**
     * Decode the ID into a Fastly item key.
     *
     * @param string $redirect
     * @return string
     */
    public static function decodeId($id)
    {
        return urlencode(base64_decode(str_replace('_', '/', $id)));
    }

    /**
     * Convert the object into something JSON serializable.
     *
     * @return array
     */
    public function jsonSerialize()
    {
        return $this->attributes;
    }
}
