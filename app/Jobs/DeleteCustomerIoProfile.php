<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\CustomerIo;

class DeleteCustomerIoProfile extends CustomerIoJob
{
    /**
     * The user to delete a Customer.io profile for.
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
        $customerIo->deleteCustomer($this->user->id);
    }
}
