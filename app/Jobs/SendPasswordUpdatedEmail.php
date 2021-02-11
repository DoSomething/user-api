<?php

namespace App\Jobs;

use App\Jobs\Middleware\CustomerIoRateLimit;
use App\Models\User;
use App\Services\CustomerIo;

class SendPasswordUpdatedEmail extends Job
{
    /**
     * The user to send a transactional email to.
     *
     * @var User
     */
    protected $user;

    /**
     * Create a new job instance.
     *
     * @param User $user
     * @param string $url
     * @return void
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * Get the middleware the job should pass through.
     *
     * @return array
     */
    public function middleware()
    {
        return [new CustomerIoRateLimit()];
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(CustomerIo $customerIo)
    {
        $customerIo->sendEmail(
            $this->user,
            CustomerIo::getTransactionalMessageId('PASSWORD_UPDATED'),
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
        ];
    }
}
