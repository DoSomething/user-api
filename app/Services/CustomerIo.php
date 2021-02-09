<?php

namespace App\Services;

use App\Models\User;
use Exception;

class CustomerIo
{
    /**
     * The Customer.io App API client.
     *
     * @var Client
     */
    protected $appApiClient;

    /**
     * The Customer.io Track API client.
     *
     * @var Client
     */
    protected $trackApiClient;

    /**
     * Create new clients for the Customer.io App API and Track API.
     */
    public function __construct()
    {
        $appApiConfig = config('services.customerio.app_api');

        $this->appApiClient = new \GuzzleHttp\Client([
            'base_uri' => $appApiConfig['url'],
            'headers' => [
                'Authorization' => 'Bearer ' . $appApiConfig['api_key'],
            ],
        ]);

        $trackApiConfig = config('services.customerio.track_api');

        $this->trackApiClient = new \GuzzleHttp\Client([
            'base_uri' => $trackApiConfig['url'],
            'auth' => [
                $trackApiConfig['username'],
                $trackApiConfig['password'],
            ],
        ]);
    }

    /**
     * Is Customer.io enabled for this app?
     *
     * @return bool
     */
    protected function enabled(): bool
    {
        return config('features.customer_io');
    }

    /**
     * Track Customer.io event for given user with given name and data.
     * @see https://customer.io/docs/api/#apitrackeventsevent_add
     *
     * @param User $user
     * @param string $eventName
     * @param array $eventData
     */
    public function trackEvent($user, $eventName, $eventData = [])
    {
        if (!$this->enabled()) {
            info('Event would have been sent to Customer.io', [
                'id' => $user->id,
                'name' => $eventName,
                'data' => $eventData,
            ]);

            return;
        }

        $response = $this->trackApiClient->post('customers/' . $user->id . '/events', [
            'json' => ['name' => $eventName, 'data' => $eventData],
        ]);

        // For this endpoint, any status besides 200 means something is wrong:
        if ($response->getStatusCode() !== 200) {
            throw new Exception(
                'Customer.io error: ' . (string) $response->getBody(),
            );
        }

        info('Event sent to Customer.io', [
            'id' => $user->id,
            'name' => $eventName,
        ]);
    }

    /**
     * Create or update the given customer's profile in Customer.io.
     * @see https://customer.io/docs/api/#apitrackcustomerscustomers_update
     *
     * @param User $user
     */
    public function updateCustomer(User $user)
    {
        $payload = $user->toCustomerIoPayload();

        if (!$this->enabled()) {
            info('User would have been sent to Customer.io', [
                'id' => $user->id,
                'payload' => $payload,
            ]);

            return;
        }

        $response = $this->trackApiClient->put('customers/' . $user->id, [
            'json' => $payload,
        ]);

        // For this endpoint, any status besides 200 means something is wrong:
        if ($response->getStatusCode() !== 200) {
            throw new Exception(
                'Customer.io error: ' . (string) $response->getBody(),
            );
        }

        info('User sent to Customer.io', ['id' => $user->id]);
    }

    /**
     * Delete the given user's profile in Customer.io.
     * @see https://customer.io/docs/api/#apitrackcustomerscustomers_delete
     *
     * @param string $id
     */
    public function deleteUser(string $id)
    {
        if (!config('features.delete-api')) {
            info('User ' . $id . ' would have been deleted in Customer.io.');

            return;
        }

        return $this->trackApiClient->delete('customers/' . $id);
    }

    /**
     * Sends a transactional email.
     * @see https://customer.io/docs/api/#operation/sendEmail
     *
     * @param string $to
     * @param int $transactionalMessageId
     * @param array $messageData
     */
    public function sendEmail($to, $transactionalMessageId, $messageData = [])
    {
        if (!$this->enabled()) {
            info('Transactional email would have been sent from Customer.io', [
                'transactional_message_id' => $transactionalMessageId,
                'data' => $messageData,
            ]);

            return;
        }

        $payload = [
            'identifiers' => [
                'id' => config('services.customerio.app_api.identifier_id'),
            ],
            'to' => $to,
            'transactional_message_id' => $transactionalMessageId,
        ];

        if ($messageData) {
            $payload['message_data'] = $messageData;
        }

        $response = $this->appApiClient->post('send/email', ['json' => $payload]);
    }
}
