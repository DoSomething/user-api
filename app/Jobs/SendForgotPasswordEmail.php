<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\CustomerIo;

class SendForgotPasswordEmail extends CustomerIoJob
{
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
        $this->url = $url;
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
            CustomerIo::getTransactionalMessageId('FORGOT_PASSWORD'),
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
            'url' => $this->url,
            'user' => $this->user,
        ];
    }
}
