<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\CustomerIo;

class SendPasswordUpdatedEmail extends CustomerIoJob
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
