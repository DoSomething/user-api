<?php

namespace Northstar\Listeners;

use Illuminate\Support\Facades\Log;

class ReportThrottledRequest
{
    /**
     * Handle the event.
     *
     * @return void
     */
    public function handle()
    {
        Log::warning('rate_limited_request');
    }
}
