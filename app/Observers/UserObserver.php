<?php

namespace Northstar\Observers;

use Northstar\Models\User;
use Northstar\Models\RefreshToken;
use Northstar\Services\CustomerIo;
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

        // And finally, delete the user's profile in Customer.io:
        app(CustomerIo::class)->deleteUser($user);

        info('Deleted: '.$user->id);
    }
}
