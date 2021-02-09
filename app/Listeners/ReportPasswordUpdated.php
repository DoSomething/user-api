<?php

namespace App\Listeners;

use App\Events\PasswordUpdated;
use App\Jobs\CreateCustomerIoEvent;
use App\Jobs\SendCustomerIoEmail;
use Illuminate\Support\Str;

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
        /*
         * Use Customer.io events for updates via account activation, so admins can customize the
         * user's journey based on their source (e.g., Rock The Vote, newsletter subscription).
         */
        if (Str::contains($event->updatedVia, 'activate-account')) {
            return CreateCustomerIoEvent::dispatch(
                $event->user,
                /*
                 * TODO: We should rename this as activate-accounted now that we no longer
                 * will use Customer.io events for standard forgot-password or profile updates.
                 */
                'password_updated',
                [
                    'updated_via' => $event->updatedVia,
                ]
            );
        }

        // Use Transactional API for other updates (e..g, profile, forgot password email).
        SendCustomerIoEmail::dispatch(
            $event->user->email,
            config('services.customerio.app_api.transactional_message_ids.password_updated')
        );
    }
}
