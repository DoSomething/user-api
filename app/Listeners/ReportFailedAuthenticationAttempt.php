<?php

namespace App\Listeners;

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
        Log::warning('failed_authentication_attempt');
    }
}
