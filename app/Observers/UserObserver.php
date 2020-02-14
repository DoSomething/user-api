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
        // Subscribe user to email if topics have been provided.
        if (isset($user->email_subscription_topics) && count($user->email_subscription_topics)) {
            $user->email_subscription_status = true;
        }

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
        $changed = $user->getDirty();

        // If we're unsubscribing from email, clear all topics.
        if (isset($changed['email_subscription_status']) && ! $changed['email_subscription_status']) {
            $user->email_subscription_topics = [];
        /**
         * Else if we are updating topics, ensure email subscription status is true.
         *
         * Note: We intentionally do not auto-unsubscribe if we're updating topics with an empty array.
         * @see https://www.pivotaltracker.com/n/projects/2401401/stories/170599403/comments/211127349.
         */
        } elseif (isset($changed['email_subscription_topics']) && count($changed['email_subscription_topics']) && ! $user->email_subscription_status) {
            $user->email_subscription_status = true;
        }

        // Write profile changes to the log, with redacted values for hidden fields.
        if (! app()->runningInConsole()) {
            logger('updated user', ['id' => $user->id, 'changed' => $user->getChanged()]);
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
