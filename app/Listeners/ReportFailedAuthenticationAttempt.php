<?php

namespace Northstar\Listeners;

use Illuminate\Support\Facades\Log;

class ReportFailedAuthenticationAttempt
{
    /**
     * Handle the event.
     *
     * @return void
     */
    public function handle()
    {
        Log::warning('failed user authentication attempt');
    }
}
