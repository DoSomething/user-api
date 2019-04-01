<?php

namespace Northstar\Listeners;

use Northstar\Events\PasswordUpdated;
use Northstar\Jobs\SendPasswordUpdatedToCustomerIo;

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
        SendPasswordUpdatedToCustomerIo::dispatch($event->user, $event->updatedVia);
    }
}
