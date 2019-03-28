<?php

namespace Northstar\Listeners;

use Illuminate\Auth\Events\PasswordReset;

class ReportPasswordUpdated
{
    /**
     * The Customer.io client.
     *
     * @var Client
     */
    protected $client;

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        $config = config('services.customerio');

        $this->client = new \GuzzleHttp\Client([
            'base_uri' => $config['url'],
            'auth' => [$config['username'], $config['password']],
        ]);
    }

    /**
     * Handle the event.
     *
     * @param PasswordReset $event
     * @return void
     */
    public function handle(PasswordReset $event)
    {
        $response = $this->client->post('customers/'.$event->user->id.'/events', [
            'form_params' => ['name' => 'password_updated'],
        ]);
    }
}
