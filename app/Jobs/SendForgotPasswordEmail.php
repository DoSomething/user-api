<?php

namespace App\Jobs;

use App\Jobs\Middleware\CustomerIoRateLimit;
use App\Models\User;
use App\Services\CustomerIo;

class SendForgotPasswordEmail extends Job
{
    /**
     * The Customer.io transactional message ID to use for email content.
     *
     * @var int
     */
    protected $transactionalMessageId;

    /**
     * The user to send a transactional email to.
     *
     * @var User
     */
    protected $user;

    /**
     * The password reset URL to send to the user.
     *
     * @var string
     */
    protected $url;

    /**
     * Create a new job instance.
     *
     * @param User $user
     * @param string $url
     * @return void
     */
    public function __construct(User $user, string $url)
    {
        $this->user = $user;
        $this->transactionalMessageId = CustomerIo::getTransactionalMessageIds('forgot_password');
        $this->url = $url;
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
            $this->user->email,
            $this->transactionalMessageId,
            ['url' => $this->url],
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
            'transactionalMessageId' => $this->transactionalMessageId,
            'url' => $this->messageData,
            'user' => $this->user,
        ];
    }
}
