<?php

namespace Northstar\Http\Controllers;

use Northstar\Models\User;
use Illuminate\Http\Request;
use Northstar\Http\Transformers\UserTransformer;

class DeletionRequestController extends Controller
{
    /**
     * @var UserTransformer
     */
    protected $transformer;

    /**
     * Make a new UserController, inject dependencies,
     * and set middleware for this controller's methods.
     *
     * @param UserTransformer $transformer
     */
    public function __construct(UserTransformer $transformer)
    {
        $this->transformer = $transformer;

        $this->middleware('scope:user');
        $this->middleware('scope:write');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  User $user
     * @param  Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(User $user, Request $request)
    {
        $this->authorize('request-deletion', $user);

        $user->deletion_requested_at = now();

        // We'll also automatically unsubscribe users from marketing:
        $user->email_subscription_status = false;
        $user->email_subscription_topics = [];
        $user->sms_status = 'stop';

        $user->save();

        info('created_deletion_request', ['id' => $user->id]);

        return $this->item($user);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  User $user
     * @return \Illuminate\Http\Response
     */
    public function destroy(User $user)
    {
        $this->authorize('request-deletion', $user);

        $user->deletion_requested_at = null;
        $user->save();

        info('revoked_deletion_request', ['id' => $user->id]);

        return $this->item($user);
    }
}
