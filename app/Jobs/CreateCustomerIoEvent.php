<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\CustomerIo;
use Illuminate\Support\Facades\Redis;

class CreateCustomerIoEvent extends Job
{
    /**
     * The serialized user model.
     *
     * @var User
     */
    protected $user;

    /**
     * The name of the event to create.
     *
     * @var string
     */
    protected $eventName;

    /**
     * The payload of the event to create.
     *
     * @var array
     */
    protected $eventData;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(User $user, $eventName, $eventData)
    {
        $this->user = $user;
        $this->eventName = $eventName;
        $this->eventData = $eventData;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(CustomerIo $customerIo)
    {
        // Rate limit Customer.io API requests to 10/s.
        $throttler = Redis::throttle('customerio')
            ->allow(10)
            ->every(1);

        $throttler->then(
            function () use ($customerIo) {
                $response = $customerIo->trackEvent(
                    $this->user,
                    $this->eventName,
                    $this->eventData,
                );

                info(
                    "Sent {$this->eventName} event for {$this->user->id} to Customer.io",
                    ['response' => $response],
                );
            },
            function () {
                return $this->release(10);
            },
        );
    }

    /**
     * Return the parameters passed to the job.
     *
     * @return array
     */
    public function getParams()
    {
        return [
            'user' => $this->user,
            'eventName' => $this->eventName,
            'eventData' => $this->eventData,
        ];
    }
}
