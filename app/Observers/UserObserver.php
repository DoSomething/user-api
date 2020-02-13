<?php

namespace Northstar\Observers;

use Northstar\Models\User;
use Northstar\Jobs\SendUserToCustomerIo;

class UserObserver
{
    /**
     * Handle the User "creating" event.
     *
     * @param  \Northstar\Models\User  $user
     * @return void
     */
    public function creating(User $user)
    {
        // Set source automatically if not provided.
        $user->source = $user->source ?: client_id();
    }

    /**
     * Handle the User "created" event.
     *
     * @param  \Northstar\Models\User  $user
     * @return void
     */
    public function created(User $user)
    {
        // Send payload to Blink for Customer.io profile.
        $queueLevel = config('queue.jobs.users');
        $queue = config('queue.names.'.$queueLevel);
        SendUserToCustomerIo::dispatch($user)->onQueue($queue);

        // Send metrics to StatHat.
        app('stathat')->ezCount('user created');
        app('stathat')->ezCount('user created - '.$user->source);
    }

    /**
     * Handle the User "updating" event.
     *
     * @param  \Northstar\Models\User  $user
     * @return void
     */
    public function updating(User $user)
    {
        /**
         * If any email subscription topics exist, ensure that email subscription status is true.
         *
         * Note: We're intentionally not checking for inverse of unsubscribing if topics is empty,
         * @see https://www.pivotaltracker.com/n/projects/2401401/stories/170599403/comments/211127349.
         */
        if (isset($user->email_subscription_topics) && count($user->email_subscription_topics) && ! $user->email_subscription_status) {
            $user->email_subscription_status = true;
        }

        // Write profile changes to the log, with redacted values for hidden fields.
        $changed = $user->getChanged();

        if (! app()->runningInConsole()) {
            logger('updated user', ['id' => $user->id, 'changed' => $changed]);
        }
    }

    /**
     * Handle the User "updated" event.
     *
     * @param  \Northstar\Models\User  $user
     * @return void
     */
    public function updated(User $user)
    {
        // Send payload to Blink for Customer.io profile.
        $queueLevel = config('queue.jobs.users');
        $queue = config('queue.names.'.$queueLevel);
        SendUserToCustomerIo::dispatch($user)->onQueue($queue);
    }
}
