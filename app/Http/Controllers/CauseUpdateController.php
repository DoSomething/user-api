<?php

namespace Northstar\Http\Controllers;

use Northstar\Models\User;
use Northstar\Http\Transformers\UserTransformer;

class CauseUpdateController extends Controller
{
    /**
     * @var UserTransformer
     */
    protected $transformer;

    /**
     * Make a new CauseUpdateController, inject dependencies,
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

    public function store(User $user, string $cause)
    {
        $this->authorize('edit-profile', $user);

        if (! in_array($cause, ['news', 'scholarships', 'community', 'lifestyle'])) {
            abort(404, 'That subscription does not exist.');
        }

        $user->push('email_subscription_topics', $cause, true);

        return $this->item($user);
    }

    public function destroy(User $user, string $cause)
    {
        $this->authorize('edit-profile', $user);

        if (! in_array($cause, ['news', 'scholarships', 'community', 'lifestyle'])) {
            abort(404, 'That subscription does not exist.');
        }

        $user->pull('email_subscription_topics', $cause);

        return $this->item($user);
    }
}