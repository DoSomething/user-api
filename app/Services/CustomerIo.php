<?php

namespace App\Services;

use App\Models\User;
use Exception;

class CustomerIo
{
    /**
     * The Customer.io App API client.
     *
     * @var \GuzzleHttp\Client
     */
    protected $appApiClient;

    /**
     * The Customer.io Track API client.
     *
     * @var \GuzzleHttp\Client
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

        $payload = ['name' => $eventName];

        if ($eventData) {
            $payload['data'] = $eventData;
        }

        $response = $this->trackApiClient->post(
            'customers/' . $user->id . '/events',
            ['json' => $payload]
        );

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
     * Read the given customer's profile in Customer.io.
     * @see https://customer.io/docs/api/#operation/getPersonAttributes
     *
     * @param User $user
     */
    public function getAttributes(User $user)
    {
        if (!$this->enabled()) {
            info('Would have read attributes from Customer.io', [
                'id' => $user->id,
            ]);

            return null;
        }

        $response = $this->appApiClient->get(
            "https://beta-api.customer.io/v1/api/customers/$user->id/attributes",
        );

        // For this endpoint, any status besides 200 means something is wrong:
        if ($response->getStatusCode() !== 200) {
            return null;
        }

        $json = json_decode((string) $response->getBody(), true);

        return $json['customer']['attributes'];
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
     * Delete the given user's Customer.io profile.
     * @see https://customer.io/docs/api/#operation/delete
     *
     * @param string $id
     */
    public function deleteCustomer(string $id)
    {
        logger('Deleting Customer.io profile', ['user_id' => $id]);

        return $this->trackApiClient->delete('customers/' . $id);
    }

    /**
     * Suppress the given user's Customer.io profile.
     * @see https://customer.io/docs/api/#operation/suppress
     *
     * @param string $id
     */
    public function suppressCustomer(string $id)
    {
        if (!config('features.delete-api')) {
            info('User ' . $id . ' would have been suppressed in Customer.io.');

            return;
        }

        logger('Suppressing Customer.io profile', ['user_id' => $id]);

        return $this->trackApiClient->post('customers/' . $id . '/suppress', ['json' => []]);
    }

    /**
     * Sends a transactional email.
     * @see https://customer.io/docs/api/#operation/sendEmail
     *
     * @param User $user
     * @param int $transactionalMessageId
     * @param array $messageData
     */
    public function sendEmail(User $user, $transactionalMessageId, $messageData = [])
    {
        $logInfo = [
            'transactional_message_id' => $transactionalMessageId,
            'data' => $messageData,
        ];

        if (!$this->enabled()) {
            info('Transactional email would have been sent from Customer.io', $logInfo);

            return;
        }

        $payload = [
            'identifiers' => [
                'id' => config('services.customerio.app_api.identifier_id'),
            ],
            'to' => $user->email,
            'transactional_message_id' => $transactionalMessageId,
        ];

        if ($messageData) {
            $payload['message_data'] = $messageData;
        }

        logger('Sending Customer.io email', $logInfo);

        $response = $this->appApiClient->post('send/email', ['json' => $payload]);
    }

    /**
     * Returns the Transactional Message ID to use for a given email type.
     *
     * @param string $emailType
     * @return int
     */
    public static function getTransactionalMessageId($emailType)
    {
        $ids = config('services.customerio.app_api.transactional_message_ids');

        return $ids[$emailType];
    }
}
