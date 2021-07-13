<?php

namespace App\Observers;

use App\Jobs\CreateCustomerIoEvent;
use App\Jobs\DeleteCustomerIoProfile;
use App\Jobs\DeleteUserFromOtherServices;
use App\Jobs\UpsertCustomerIoProfile;
use App\Models\RefreshToken;
use App\Models\User;
use Carbon\Carbon;
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
        // Send metrics to StatHat.
        Log::info('user_created', ['source' => $user->source]);

        // If user is not subscribed to any platform -- do not create a Customer.io profile.
        if (!($user->email_subscription_status || $user->isSmsSubscribed())) {
            return;
        }

        $user->calculateUserSubscriptionBadges();

        $queueLevel = config('queue.jobs.users');
        $queue = config('queue.names.' . $queueLevel);

        UpsertCustomerIoProfile::dispatch($user)->onQueue($queue);
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
        $currentlySubscribed = [
            'email' => $user->email_subscription_status,
            'sms' => User::isSubscribedSmsStatus($user->sms_status),
        ];
        $isSubscribing = [
            // Users subscribe to email by selecting topics from their Subscriptions profile page.
            'email' =>
                isset($changed['email_subscription_topics']) &&
                count($changed['email_subscription_topics']),
            // Initialize as false for now (will get set later if subscribing to SMS).
            'sms' => false,
        ];
        $isUnsubscribing = [
            'email' =>
                isset($changed['email_subscription_status']) &&
                !$changed['email_subscription_status'],
            // Initialize as false for now (will get set later if unsubscribing to SMS).
            'sms' => false,
        ];

        // If we're unsubscribing from email, clear all topics.
        if ($isUnsubscribing['email']) {
            $user->email_subscription_topics = [];
        } elseif (
            /*
             * Else if we are updating topics, ensure email subscription status is true.
             *
             * Note: We intentionally do not auto-unsubscribe if we're updating topics with an empty array.
             * @see https://www.pivotaltracker.com/n/projects/2401401/stories/170599403/comments/211127349.
             */
            $isSubscribing['email'] &&
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
                $isUnsubscribing['sms'] = true;

                $user->clearSmsSubscriptionTopics();
            } elseif (User::isSubscribedSmsStatus($changed['sms_status'])) {
                $isSubscribing['sms'] = true;

                // Set default SMS topics if user has none.
                if (
                    !isset($changed['sms_subscription_topics']) &&
                    !$user->hasSmsSubscriptionTopics()
                ) {
                    $user->addDefaultSmsSubscriptionTopics();
                }
            }
        }

        // If promotions are muted and this update is resubscribing, unmute promotions.
        if (
            isset($user->promotions_muted_at) &&
            ($isSubscribing['sms'] || $isSubscribing['email'])
        ) {
            $user->promotions_muted_at = null;
        }

        // If this update means user will be unsubscribed from both platforms, mute promotions.
        if (
            ($isUnsubscribing['sms'] && $isUnsubscribing['email']) ||
            ($isUnsubscribing['sms'] && !$currentlySubscribed['email']) ||
            ($isUnsubscribing['email'] && !$currentlySubscribed['sms'])
        ) {
            $user->promotions_muted_at = Carbon::now();
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
        $changed = $user->getChanged();

        // If we're updating a user's club, dispatch a Customer.io event.
        if (isset($changed['club_id'])) {
            $customerIoPayload = $user->getClubIdUpdatedEventPayload(
                $changed['club_id'],
            );

            // We'll only dispatch the event if the club is valid and we have the expected event payload.
            if ($customerIoPayload) {
                $user->trackCustomerIoEvent(
                    'club_id_updated',
                    $customerIoPayload,
                );
            }
        }

        // Write profile changes to the log, with redacted values for hidden fields.
        if (!app()->runningInConsole()) {
            logger('updated user', [
                'id' => $user->id,
                'changed' => $changed,
            ]);
        }

        // Handle any changes to the user's subscriptions/promotions.
        $user->backfillBadges();

        $mutedPromotions = isset($user->promotions_muted_at);
        $shouldTrackPromotionsResubscribe = false;

        // If we just made a change to mute promotions:
        if ($user->wasChanged('promotions_muted_at')) {
            // And we set it, delete the Customer.io profile.
            if ($mutedPromotions) {
                return DeleteCustomerIoProfile::dispatch($user)->onQueue(
                    config('queue.names.low'),
                );
            }

            // Otherwise, it's null and we need to track resubscribe.
            $shouldTrackPromotionsResubscribe = true;
        }

        // If this user has promotions muted, don't send updates to Customer.io.
        if ($mutedPromotions) {
            if (!app()->runningInConsole()) {
                logger('Skipping profile update for muted user', [
                    'id' => $user->id,
                ]);
            }

            return;
        }

        $queueLevel = config('queue.jobs.users');
        $queue = config('queue.names.' . $queueLevel);

        if ($shouldTrackPromotionsResubscribe) {
            UpsertCustomerIoProfile::withChain([
                // TODO: Refactor this to be a new TrackPromotionsResubscribeCustomerIoEvent job.
                new CreateCustomerIoEvent($user, 'promotions_resubscribe', []),
            ])
                ->onQueue($queue)
                ->dispatch($user);

            return;
        }

        return UpsertCustomerIoProfile::dispatch($user);
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

        // @see: PostObserver@deleting' & SignupObserver@deleting
        $user->posts->each->delete();
        $user->signups->each->delete();

        // Delete refresh tokens to end any active sessions:
        RefreshToken::where('user_id', $user->id)->delete();

        // And finally, delete the user from other services:
        DeleteUserFromOtherServices::dispatch($user->id);

        info('Deleted: ' . $user->id);
    }
}
