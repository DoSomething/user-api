<?php

namespace Northstar\Services;

use DoSomething\Gateway\Common\RestApiClient;

class Fastly extends RestApiClient
{
    /**
     * Create a new Fastly API client.
     */
    public function __construct()
    {
        $url = config('services.fastly.url');

        $options = [
            'headers' => [
                'Fastly-Key' => config('services.fastly.key'),
                'Accept'     => 'application/json',
            ],
        ];

        parent::__construct($url, $options);
    }

    /**
     * Purge object from Fastly cache based on give cache key
     *
     * @param $cacheKey String
     */
    public function purgeKey($cacheKey)
    {
        $fastlyConfigured = ! is_null(config('services.fastly.url')) &&
            ! is_null(config('services.fastly.key')) &&
            ! is_null(config('services.fastly.service_id')) &&
            isset($cacheKey);

        if (! $fastlyConfigured) {
            info('image_cache_purge_failed', ['response' => 'Fastly not configured correctly on this environment.']);

            return null;
        }

        $service = config('services.fastly.service_id');

        $purgeResponse = $this->post('service/'.$service.'/purge/'.$cacheKey);

        info('image_cache_purge_successful', ['response' => $purgeResponse]);

        return $purgeResponse;
    }
}
