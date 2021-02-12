<?php

namespace App\Observers;

use App\Jobs\CreateCustomerIoEvent;
use App\Jobs\DeleteCustomerIoProfile;
use App\Jobs\DeleteUserFromOtherServices;
use App\Jobs\UpsertCustomerIoProfile;
use App\Models\RefreshToken;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;

class UserObserver
{
    /**
     * Handle the User "creating" event.
     *
     * @param  \App\Models\User  $user
     * @return void
     */
    public function creating(User $user)
    {
        // Subscribe user to email if topics have been provided.
        if (
            isset($user->email_subscription_topics) &&
            count($user->email_subscription_topics)
        ) {
            $user->email_subscription_status = true;
        }

        // Populate default topics if subscribing to SMS without any topics provided.
        if ($user->isSmsSubscribed() && !$user->hasSmsSubscriptionTopics()) {
            $user->addDefaultSmsSubscriptionTopics();
        }

        // Set source automatically if not provided.
        $user->source = $user->source ?: client_id();
    }

    /**
     * Handle the User "created" event.
     *
     * @param  \App\Models\User  $user
     * @return void
     */
    public function created(User $user)
    {
        // Send payload to Blink for Customer.io profile.
        $queueLevel = config('queue.jobs.users');
        $queue = config('queue.names.' . $queueLevel);
        UpsertCustomerIoProfile::dispatch($user)->onQueue($queue);

        // Send metrics to StatHat.
        Log::info('user_created', ['source' => $user->source]);
    }

    /**
     * Handle the User "updating" event.
     *
     * @param  \App\Models\User  $user
     * @return void
     */
    public function updating(User $user)
    {
        $changed = $user->getDirty();

        // If we're setting the promotions muted field, delete Customer.io profile and exit.
        if (isset($changed['promotions_muted_at'])) {
            info('Deleting Customer.io profile for user ' . $user->id);

            return DeleteCustomerIoProfile::dispatch($user);
        }

        // @TODO: If a muted user is re-subscribing, unset the promotions_muted_at field.

        // If we're unsubscribing from email, clear all topics.
        if (
            isset($changed['email_subscription_status']) &&
            !$changed['email_subscription_status']
        ) {
            $user->email_subscription_topics = [];
        } elseif (
            /*
             * Else if we are updating topics, ensure email subscription status is true.
             *
             * Note: We intentionally do not auto-unsubscribe if we're updating topics with an empty array.
             * @see https://www.pivotaltracker.com/n/projects/2401401/stories/170599403/comments/211127349.
             */
            isset($changed['email_subscription_topics']) &&
            count($changed['email_subscription_topics']) &&
            !$user->email_subscription_status
        ) {
            $user->email_subscription_status = true;
        }

        if (isset($changed['sms_status'])) {
            /*
             * If we're unsubscribing from SMS, clear all topics.
             *
             * Note: We don't allow users to set their own SMS subscription topics yet, so there
             * isn't a need to change sms_status if an unsubscribed user adds a SMS topic.
             */
            if (User::isUnsubscribedSmsStatus($changed['sms_status'])) {
                $user->clearSmsSubscriptionTopics();
            } elseif (
                // If resubscribing and not adding topics, add the default topics if user has none.
                User::isSubscribedSmsStatus($changed['sms_status']) &&
                !isset($changed['sms_subscription_topics']) &&
                !$user->hasSmsSubscriptionTopics()
            ) {
                $user->addDefaultSmsSubscriptionTopics();
            }
        }

        // If we're updating a user's club, dispatch a Customer.io event.
        if (isset($changed['club_id'])) {
            $customerIoPayload = $user->getClubIdUpdatedEventPayload(
                $changed['club_id'],
            );

            // We'll only dispatch the event if the club is valid and we have the expected event payload.
            if ($customerIoPayload) {
                CreateCustomerIoEvent::dispatch(
                    $user,
                    'club_id_updated',
                    $customerIoPayload,
                );
            }
        }

        // Write profile changes to the log, with redacted values for hidden fields.
        if (!app()->runningInConsole()) {
            logger('updated user', [
                'id' => $user->id,
                'changed' => $user->getChanged(),
            ]);
        }
    }

    /**
     * Handle the User "updated" event.
     *
     * @param  \App\Models\User  $user
     * @return void
     */
    public function updated(User $user)
    {
        // If this user has promotions muted, we don't want to send updates to Customer.io.
        if (isset($user->promotions_muted_at)) {
            if (!app()->runningInConsole()) {
                logger('Skipping profile update for muted user', [
                    'id' => $user->id,
                ]);
            }

            return;
        }

        // Send payload to Blink for Customer.io profile.
        $queueLevel = config('queue.jobs.users');
        $queue = config('queue.names.' . $queueLevel);
        UpsertCustomerIoProfile::dispatch($user)->onQueue($queue);
    }

    /**
     * Handle the User "deleting" event.
     *
     * @param  \App\Models\User  $user
     * @return void
     */
    public function deleting(User $user)
    {
        // Anonymize birthdate so we can see demographics of deleted users:
        if ($user->birthdate) {
            $user->update(['birthdate' => $user->birthdate->year . '-01-01']);
        }

        // Remove all fields except some non-identifiable demographics:
        $fields = array_keys(
            Arr::except($user->getAttributes(), [
                '_id',
                'birthdate',
                'language',
                'source',
                'source_detail',
                'addr_city',
                'addr_state',
                'addr_zip',
                'country',
                'created_at',
                'updated_at',
            ]),
        );

        if ($fields) {
            $user->drop($fields);
        }

        // Delete refresh tokens to end any active sessions:
        $token = RefreshToken::where('user_id', $user->id)->delete();

        // And finally, delete the user from other services:
        DeleteUserFromOtherServices::dispatch($user->id);

        info('Deleted: ' . $user->id);
    }
}
