<?php

namespace App\Jobs\Middleware;

use Illuminate\Support\Facades\Redis;

class CustomerIoRateLimit
{
    /**
     * Process the queued job.
     *
     * @param  mixed  $job
     * @param  callable  $next
     * @return mixed
     */
    public function handle($job, $next)
    {
        // Rate limit to 10 requests/second.
        $throttler = Redis::throttle('customerio')
            ->allow(10)
            ->every(1);

        $throttler->then(
            function () use ($job, $next) {
                // Lock obtained, run job...
                $next($job);
            },
            function () use ($job) {
                // If we  can't obtain a lock, release back to queue...
                $job->release(10);
            },
        );
    }
}
