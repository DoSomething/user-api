<?php

namespace Northstar\Http\Controllers;

use Northstar\Models\User;
use Northstar\Http\Transformers\UserTransformer;

class SubscriptionUpdateController extends Controller
{
    /**
     * @var UserTransformer
     */
    protected $transformer;

    /**
     * @var array
     */
    protected $emailSubscriptionTopics = ['news', 'scholarships', 'community', 'lifestyle', 'clubs'];

    /**
     * Make a new SubpscriptionUpdateController, inject dependencies,
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

    public function store(User $user, string $topic)
    {
        $this->authorize('edit-profile', $user);

        if (! in_array($topic, $this->emailSubscriptionTopics)) {
            abort(404, 'That subscription does not exist.');
        }

        $user->addEmailSubscriptionTopic($topic);

        $user->save();

        return $this->item($user);
    }

    public function destroy(User $user, string $topic)
    {
        $this->authorize('edit-profile', $user);

        if (! in_array($topic, $this->emailSubscriptionTopics)) {
            abort(404, 'That subscription does not exist.');
        }

        $user->pull('email_subscription_topics', $topic);

        return $this->item($user);
    }
}
