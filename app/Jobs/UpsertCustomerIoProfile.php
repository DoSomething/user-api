<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\CustomerIo;

class UpsertCustomerIoProfile extends CustomerIoJob
{
    /**
     * The serialized user model.
     *
     * @var User
     */
    protected $user;

    /**
     * Create a new job instance.
     *
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
        $customerIo->updateCustomer($this->user);
    }
}
