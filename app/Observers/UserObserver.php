<?php

namespace Northstar\Observers;

use Northstar\Models\User;
use Northstar\Models\RefreshToken;
use Illuminate\Support\Facades\Log;
use Northstar\Jobs\SendUserToCustomerIo;
use Northstar\Jobs\DeleteUserFromOtherServices;

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

        // Populate default topics if subscribing to SMS without any topics provided.
        if ($user->isSmsSubscribed() && ! $user->hasSmsSubscriptionTopics()) {
            $user->addDefaultSmsSubscriptionTopics();
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
        Log::info('user_created', ['source' => $user->source]);
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

        if (isset($changed['sms_status'])) {
            /**
             * If we're unsubscribing from SMS, clear all topics.
             *
             * Note: We don't allow users to set their own SMS subscription topics yet, so there
             * isn't a need to change sms_status if an unsubscribed user adds a SMS topic.
             */
            if (User::isUnsubscribedSmsStatus($changed['sms_status'])) {
                $user->clearSmsSubscriptionTopics();
            // If resubscribing and not adding topics, add the default topics if user has none.
            } elseif (User::isSubscribedSmsStatus($changed['sms_status']) && ! isset($changed['sms_subscription_topics']) && ! $user->hasSmsSubscriptionTopics()) {
                $user->addDefaultSmsSubscriptionTopics();
            }
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

    /**
     * Handle the User "deleting" event.
     *
     * @param  \Northstar\Models\User  $user
     * @return void
     */
    public function deleting(User $user)
    {
        // Anonymize birthdate so we can see demographics of deleted users:
        if ($user->birthdate) {
            $user->update(['birthdate' => $user->birthdate->year.'-01-01']);
        }

        // Remove all fields except some non-identifiable demographics:
        $fields = array_keys(array_except($user->getAttributes(), [
            '_id', 'birthdate', 'language', 'source', 'source_detail',
            'addr_city', 'addr_state', 'addr_zip', 'country',
            'created_at', 'updated_at',
        ]));

        if ($fields) {
            $user->drop($fields);
        }

        // Delete refresh tokens to end any active sessions:
        $token = RefreshToken::where('user_id', $user->id)->delete();

        // And finally, delete the user from other services:
        DeleteUserFromOtherServices::dispatch($user->id);

        info('Deleted: '.$user->id);
    }
}
