<?php

namespace Northstar\Http\Controllers;

use Northstar\Models\User;
use Illuminate\Http\Request;
use Northstar\Http\Transformers\UserTransformer;

class SubscriptionUpdateController extends Controller 
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

    public function store(User $user, string $topic)
    {
      $this->authorize('edit-profile', $user);

      if (!in_array($topic, ['news', 'scholarships', 'community', 'lifestyle'])) {
        abort(404, 'That subscription does not exist.');
      }

      $user->push('email_subscription_topics', $topic, true);

      return $this->item($user);
    }

    public function destroy(User $user, string $topic)
    {
      $this->authorize('edit-profile', $user);

      if (!in_array($topic, ['news', 'scholarships', 'community', 'lifestyle'])) {
        abort(404, 'That subscription does not exist.');
      }

      $user->pull('email_subscription_topics', $topic);

      return $this->item($user);
    }

}