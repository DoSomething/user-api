<?php

namespace App\Jobs;

use App\Jobs\Middleware\CustomerIoRateLimit;

class CustomerIoJob extends Job
{
    /**
     * Get the middleware the job should pass through.
     *
     * @return array
     */
    public function middleware()
    {
        return [new CustomerIoRateLimit()];
    }
}
