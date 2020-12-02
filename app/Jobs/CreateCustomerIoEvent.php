<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\CustomerIo;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Redis;

class CreateCustomerIoEvent implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

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
}
