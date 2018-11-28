<?php

namespace Northstar\Auth;

use Closure;
use Exception;
use Northstar\Models\User;
use Illuminate\Http\Request;
use Illuminate\Contracts\Hashing\Hasher;
use Illuminate\Validation\Factory as Validation;
use Northstar\Exceptions\NorthstarValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Contracts\Auth\Authenticatable as UserContract;

class Registrar
{
    /**
     * Laravel's validation factory.
     *
     * @var Validation
     */
    protected $validation;

    /**
     * The hasher implementation.
     *
     * @var \Illuminate\Contracts\Hashing\Hasher
     */
    protected $hasher;

    /**
     * Registrar constructor.
     *
     * @param Validation $validation
     * @param Hasher $hasher
     */
    public function __construct(Validation $validation, Hasher $hasher)
    {
        $this->validation = $validation;
        $this->hasher = $hasher;
    }

    /**
     * Validate the given user and request.
     *
     * @param Request $request
     * @param User $existingUser
     * @param array $additionalRules
     * @throws NorthstarValidationException
     */
    public function validate(Request $request, User $existingUser = null, array $additionalRules = [])
    {
        $fields = normalize('credentials', $request->all());

        $existingId = isset($existingUser->id) ? $existingUser->id : 'null';
        $rules = [
            'first_name' => 'max:50',
            'email' => 'email|nullable|unique:users,email,'.$existingId.',_id|required_without:mobile',
            'mobile' => 'mobile|nullable|unique:users,mobile,'.$existingId.',_id|required_without:email',
            'birthdate' => 'nullable|date',
            'country' => 'nullable|country',
            'password' => 'nullable|min:6|max:512',
            'mobilecommons_status' => 'in:active,undeliverable,unknown', // for backwards compatibility.
            'sms_status' => 'in:active,less,stop,undeliverable,unknown,pending',
            'sms_paused' => 'boolean',
            'last_messaged_at' => 'date',
            'email_frequency' => 'in:active,none',
            'voter_registration_status' => 'in:uncertain,ineligible,confirmed,registration_complete',
        ];

        // If existing user is provided, merge indexes into the request so
        // that we can validate that they exist on the "updated" document.
        if ($existingUser) {
            $fields = array_merge($existingUser->indexes(), $fields);
        }

        $validator = $this->validation->make($fields, array_merge($rules, $additionalRules));

        if ($validator->fails()) {
            throw new NorthstarValidationException($validator->errors()->getMessages());
        }
    }

    /**
     * Resolve a user account from the given credentials. This will only
     * take into account unique indexes on the User.
     *
     * @param Request|array $credentials
     * @return User|null
     */
    public function resolve($credentials)
    {
        $credentials = normalize('credentials', $credentials);

        $matches = (new User)->query();

        // For the first `where` query, we want to limit results... from then on,
        // we want to append (e.g. `SELECT * WHERE _ OR WHERE _ OR WHERE _`)
        $firstWhere = true;
        foreach (User::$uniqueIndexes as $type) {
            if (isset($credentials[$type])) {
                $matches = $matches->where($type, '=', $credentials[$type], ($firstWhere ? 'and' : 'or'));
                $firstWhere = false;
            }
        }

        // If we did not query by any fields, return null.
        if ($firstWhere) {
            return null;
        }

        // If we found one user, return it.
        $matches = $matches->get();
        if (count($matches) == 1) {
            return $matches[0];
        }

        // If we can't conclusively resolve one user so return null.
        return null;
    }

    /**
     * Resolve a user account from the given credentials, or throw
     * an exception to trigger a 404 if not able to.
     *
     * @param $credentials
     * @return User|null
     */
    public function resolveOrFail($credentials)
    {
        $user = $this->resolve($credentials);

        if (! $user) {
            throw new ModelNotFoundException;
        }

        return $user;
    }

    /**
     * Validate a user against the given credentials. If the user has a Drupal
     * password & it matches, re-hash and save to the user document.
     *
     * @param UserContract|User $user
     * @param array $credentials
     * @return bool
     */
    public function validateCredentials($user, array $credentials)
    {
        if (! $user) {
            event(new \Illuminate\Auth\Events\Failed($user, $credentials));

            return false;
        }

        if ($this->hasher->check($credentials['password'], $user->password)) {
            event(new \Illuminate\Auth\Events\Login($user, false));

            return true;
        }

        if (! $user->password && DrupalPasswordHash::check($credentials['password'], $user->drupal_password)) {
            // If this user has a Drupal-hashed password, rehash it, remove the
            // Drupal password field from the user document, and save the user.
            $user->password = $credentials['password'];
            $user->save();

            event(new \Illuminate\Auth\Events\Login($user, false));

            return true;
        }

        // Well, looks like we couldn't authenticate...
        event(new \Illuminate\Auth\Events\Failed($user, $credentials));

        return false;
    }

    /**
     * Create a new user OR update an existing user (if given).
     *
     * @param array $input - Profile fields
     * @param User $user - Optionally, user to update
     * @param Closure $customizer - Customize the user instance before saving.
     * @return User|null
     */
    public function register($input, $user = null, Closure $customizer = null)
    {
        // If there is no user provided, create a new one.
        if (! $user) {
            $user = new User;
        }

        $user->fill($input);

        if (! is_null($customizer)) {
            $customizer($user);
        }

        $user->save();

        return $user;
    }
}
