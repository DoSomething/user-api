<?php

namespace App\Managers;

use App\Jobs\CreateCustomerIoEvent;
use App\Jobs\SendSignupToCustomerIo;
use App\Repositories\SignupRepository;

class SignupManager
{
    /*
     * SignupRepository Instance
     *
     * @var App\Repositories\SignupRepository;
     */
    protected $signup;

    /**
     * Constructor.
     *
     * @param SignupRepository $signup
     */
    public function __construct(SignupRepository $signup)
    {
        $this->signup = $signup;
    }

    /**
     * Handles all business logic around creating signups.
     *
     * @param array $data
     * @param string $northstarId
     * @param int $campaignId
     * @return Illuminate\Database\Eloquent\Model $model
     */
    public function create($data, $northstarId, $campaignId)
    {
        $signup = $this->signup->create($data, $northstarId, $campaignId);

        // Send signup event(s) to Customer.io for messaging:
        SendSignupToCustomerIo::dispatch($signup);

        if ($signup->referrer_user_id) {
            CreateCustomerIoEvent::dispatch(
                $signup->referrer_user_id,
                'referral_signup_created',
                $signup->getReferralSignupEventPayload(),
            );
        }

        // Log that a signup was created.
        info('signup_created', [
            'id' => $signup->id,
            'signup_created_source' => $signup->source,
        ]);

        return $signup;
    }

    /**
     * Handles all business logic around updating signups.
     *
     * @param App\Models\Signup $signup
     * @param array $data
     * @param bool $log
     * @return App\Models\Signup $model
     */
    public function update($signup, $data, $log = true)
    {
        $signup = $this->signup->update($signup, $data);

        if ($log) {
            // Log that a signup was updated.
            info('signup_updated', ['id' => $signup->id]);
        }

        return $signup;
    }

    /**
     * Handle all business logic around deleting a signup.
     *
     * @param int $signupId
     * @return bool
     */
    public function destroy($signupId)
    {
        $trashed = $this->signup->destroy($signupId);

        if ($trashed) {
            info('signup_deleted', [
                'id' => $signupId,
            ]);
        }

        return $trashed;
    }

    /*
     * Handles all business logic around retrieving a signup.
     *
     * @param  string $northstarId
     * @param  int $campaignId
     * @return \App\Models\Signup|null
     */
    public function get($northstarId, $campaignId)
    {
        $signup = $this->signup->get($northstarId, $campaignId);

        return $signup;
    }
}
