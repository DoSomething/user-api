<?php

namespace App\Http\Controllers;

use App\Auth\Registrar;
use App\Http\Transformers\UserTransformer;
use App\Models\User;
use App\Types\EmailSubscriptionTopicType;
use App\Types\PasswordResetType;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

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
     * Make a new SubscriptionController, inject dependencies,
     * and set middleware for this controller's methods.
     */
    public function __construct(
        Registrar $registrar,
        UserTransformer $transformer
    ) {
        $this->registrar = $registrar;
        $this->transformer = $transformer;

        $this->middleware('throttle:10,60');
    }

    /**
     * Creates a new user with given email subscription topic, or adds the given topic to an existing user.
     *
     * @param Request $request
     * @return array
     */
    public function create(Request $request)
    {
        // If topics value is a string, change to an array.
        if (is_string($request->get('email_subscription_topic'))) {
            $request->merge([
                'email_subscription_topic' => [
                    $request->get('email_subscription_topic'),
                ],
            ]);
        }

        $this->validate($request, [
            'email' => 'required|email',
            'email_subscription_topic' => 'required|array',
            'email_subscription_topic.*' => [
                'string',
                Rule::in(EmailSubscriptionTopicType::all()),
            ],
            'source' => 'required',
            'source_detail' => 'required',
        ]);

        $topics = $request->get('email_subscription_topic');

        $existingUser = $this->registrar->resolve($request->only('email'));

        // If the user already exists, only update the email topics.
        if ($existingUser) {
            $existingUser->addEmailSubscriptionTopics($topics);

            $existingUser->save();

            return $this->item($existingUser, 200);
        }

        // Register new user.
        $newUser = $this->registrar->register($request->all());

        $newUser->addEmailSubscriptionTopics($topics);

        $newUser->source = $request->get('source');
        $newUser->source_detail = $request->get('source_detail');

        $newUser->save();

        // Send activate account email to new user
        switch (collect($topics)->first()) {
            case 'scholarships':
                $newUser->sendPasswordReset(
                    PasswordResetType::get('PAYS_TO_DO_GOOD_ACTIVATE_ACCOUNT'),
                );
                break;
            case 'news':
                $newUser->sendPasswordReset(
                    PasswordResetType::get('BREAKDOWN_ACTIVATE_ACCOUNT'),
                );
                break;
            case 'lifestyle':
                $newUser->sendPasswordReset(
                    PasswordResetType::get('BOOST_ACTIVATE_ACCOUNT'),
                );
                break;
            case 'community':
                $newUser->sendPasswordReset(
                    PasswordResetType::get('WYD_ACTIVATE_ACCOUNT'),
                );
                break;
        }

        return $this->item($newUser, 201);
    }
}
