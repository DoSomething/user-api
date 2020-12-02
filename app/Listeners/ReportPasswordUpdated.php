<?php

namespace App\Listeners;

use App\Events\PasswordUpdated;
use App\Jobs\SendPasswordUpdatedToCustomerIo;

class ReportPasswordUpdated
{
    /**
     * Handle the event.
     *
     * @param PasswordUpdated $event
     * @return void
     */
    public function handle(PasswordUpdated $event)
    {
        SendPasswordUpdatedToCustomerIo::dispatch(
            $event->user,
            $event->updatedVia,
        );
    }
}
