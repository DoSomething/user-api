<?php

namespace Northstar\Http\Controllers;

use Northstar\Models\User;
use Illuminate\Http\Request;
use Northstar\Auth\Registrar;
use Northstar\Http\Transformers\UserTransformer;

class SubscriptionController extends Controller
{
    /**
     * The registrar handles creating, updating, and
     * validating user accounts.
     *
     * @var Registrar
     */
    protected $registrar;

    /**
     * @var UserTransformer
     */
    protected $transformer;

     /**
     * Make a new ResetController, inject dependencies,
     * and set middleware for this controller's methods.
     */
    public function __construct(Registrar $registrar, UserTransformer $transformer)
    {
        $this->registrar = $registrar;
        $this->transformer = $transformer;

        $this->middleware('throttle:10,60');
    }

    /**
     * Creates a new user with given email subscription topic, or adds the given topic to an existing user.
     * POST v2/subscriptions
     *
     * @param Request $request
     * @return array
     */
    public function create(Request $request) {
        $this->validate($request, [
            'email' => 'required|email',
            'email_subscription_topics' => 'required',
            'email_subscription_topics.*' => 'in:news,scholarships,lifestyle,community',
            'source' => 'required',
            'source_detail' => 'required',
        ]);
        
        $existingUser = $this->registrar->resolve($request->only('email'));

        // If the user already exists, only update the email topics
        if ($existingUser) {
            foreach($request->get('email_subscription_topics') as $topic) {
                $existingUser->addEmailSubscriptionTopic($topic);   
            }

            $existingUser->save();

            return $this->item($existingUser, 200);
        }

        $newUser = $this->registrar->register($request->all());

        $newUser->source = $request->get('source');
        $newUser->source_detail =  $request->get('source_detail');

        $newUser->save();

        return $this->item($newUser, 201);
    }
}
