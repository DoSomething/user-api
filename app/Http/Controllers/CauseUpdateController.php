<?php

namespace App\Http\Controllers;

use App\Http\Transformers\UserTransformer;
use App\Models\User;
use App\Types\CauseInterestType;

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

        if (!in_array($cause, CauseInterestType::all())) {
            abort(404, 'That cause does not exist.');
        }

        $user->addCause($cause);

        $user->save();

        return $this->item($user);
    }

    public function destroy(User $user, string $cause)
    {
        $this->authorize('edit-profile', $user);

        if (!in_array($cause, CauseInterestType::all())) {
            abort(404, 'That cause does not exist.');
        }

        // Using the getter method to pull the array of causes rather than stored obj
        $causesArray = $user->causes;

        // Reassigning it to be an array with the item removed
        $user->causes = array_diff($causesArray, [$cause]);

        $user->save();

        return $this->item($user);
    }
}
