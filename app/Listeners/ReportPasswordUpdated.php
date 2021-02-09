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
         * Use Customer.io events to track account activations, so admins can customize the
         * user's messaging journey per their source (e.g., Rock The Vote, newsletter subscription).
         */
        if (Str::contains($event->updatedVia, 'activate-account')) {
            return CreateCustomerIoEvent::dispatch(
                $event->user,
                /*
                 * TODO: We should rename this event to "activated-account" now that we no longer
                 * use Customer.io events for standard forgot-password or profile updates.
                 */
                'password_updated',
                [
                    'updated_via' => $event->updatedVia,
                ]
            );
        }

        /*
         * Send transactional emails for to send confirmation for password updates that don't need
         * to be tracked (e.g., forgot password email, udpating via profile).
         */
        return SendCustomerIoEmail::dispatch(
            $event->user->email,
            config('services.customerio.app_api.transactional_message_ids.password_updated')
        );
    }
}
