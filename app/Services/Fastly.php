<?php

namespace App\Services;

use App\Services\Resources\Redirect;
use App\Services\Resources\RedirectCollection;
use DoSomething\Gateway\AuthorizesWithApiKey;
use DoSomething\Gateway\Common\RestApiClient;

class Fastly extends RestApiClient
{
    use AuthorizesWithApiKey;

    /**
     * Create a new API client.
     *
     * @param array $config
     * @param array $overrides
     */
    public function __construct($overrides = [])
    {
        $this->apiKeyHeader = 'Fastly-Key';
        $this->apiKey = config('services.fastly.api_key');

        // Resources that we'll be working with in Fastly:
        $this->frontendServiceId = config('services.fastly.services.frontend');
        $this->backendServiceId = config('services.fastly.services.backend');
        $this->redirectsTableId = config('services.fastly.redirects_table');

        parent::__construct(config('services.fastly.url'), $overrides);
    }

    /**
     * Get all redirects.
     *
     * @return RedirectCollection
     */
    public function getAllRedirects()
    {
        $redirects = $this->get(
            "service/$this->frontendServiceId/dictionary/$this->redirectsTableId/items",
        );

        $redirects = array_map(function ($redirect) {
            return Redirect::fromItems($redirect);
        }, $redirects);

        return new RedirectCollection(['data' => $redirects]);
    }

    /**
     * Get a redirect by key.
     *
     * @return Redirect
     */
    public function getRedirect($id)
    {
        $key = Redirect::decodeId($id);

        $redirect = $this->get(
            "service/$this->frontendServiceId/dictionary/$this->redirectsTableId/item/$key",
        );

        return Redirect::fromItems($redirect);
    }

    /**
     * Create a redirect.
     *
     * @return Redirect
     */
    public function createRedirect($path, $target)
    {
        // Ensure path begins with a slash & is lower-case.
        $path = $path[0] === '/' ? $path : '/' . $path;
        $path = strtolower($path);

        // Create or update a record in the redirects dictionary.
        $key = urlencode($path);
        $redirect = $this->put(
            "service/$this->frontendServiceId/dictionary/$this->redirectsTableId/item/$key",
            [
                'item_value' => $target,
            ],
        );

        return Redirect::fromItems($redirect);
    }

    /**
     * Update a redirect.
     *
     * @return Redirect
     */
    public function updateRedirect($path, $target)
    {
        // Update the corresponding record in the redirects dictionary.
        $key = urlencode($path);
        $redirect = $this->patch(
            "service/$this->frontendServiceId/dictionary/$this->redirectsTableId/item/$key",
            [
                'item_value' => $target,
            ],
        );

        return Redirect::fromItems($redirect);
    }

    /**
     * Delete a redirect.
     *
     * @return bool
     */
    public function deleteRedirect($id)
    {
        $key = Redirect::decodeId($id);

        // Delete the corresponding record in the redirects dictionary.
        return $this->delete(
            "service/$this->frontendServiceId/dictionary/$this->redirectsTableId/item/$key",
        );
    }

    /**
     * Send a POST request to the given URL.
     *
     * @param string $path - URL to make request to (relative to base URL)
     * @param array $payload - Body of the POST request
     * @param bool $withAuthorization - Should this request be authorized?
     * @return array
     */
    public function post($path, $payload = [], $withAuthorization = true)
    {
        $options = [
            'form_params' => $payload,
        ];

        return $this->send('POST', $path, $options, $withAuthorization);
    }

    /**
     * Send a PATCH request to the given URL.
     *
     * @param string $path - URL to make request to (relative to base URL)
     * @param array $payload - Body of the PUT request
     * @param bool $withAuthorization - Should this request be authorized?
     * @return array
     */
    public function patch($path, $payload = [], $withAuthorization = true)
    {
        $options = [
            'form_params' => $payload,
        ];

        return $this->send('PATCH', $path, $options, $withAuthorization);
    }

    /**
     * Determine if the response was successful or not.
     *
     * @param mixed $json
     * @return bool
     */
    public function responseSuccessful($json)
    {
        return $json['status'] === 'ok';
    }
}
