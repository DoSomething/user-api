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

        if (! in_array($cause, ['animal_welfare', 'bullying', 'education', 'environment', 'gender_rights_equality', 'homelessness_poverty', 'immigration_refugees', 'lgbtq_rights_equality', 'mental_health', 'physical_health', 'racial_justice_equity', 'sexual_harassment_assault'])) {
            abort(404, 'That cause does not exist.');
        }

        $user->addCause($cause);

        $user->save();

        return $this->item($user);
    }

    public function destroy(User $user, string $cause)
    {
        $this->authorize('edit-profile', $user);

        if (! in_array($cause, ['animal_welfare', 'bullying', 'education', 'environment', 'gender_rights_equality', 'homelessness_poverty', 'immigration_refugees', 'lgbtq_rights_equality', 'mental_health', 'physical_health', 'racial_justice_equity', 'sexual_harassment_assault'])) {
            abort(404, 'That cause does not exist.');
        }

        $causesArray = $user->causes;

        $causesArray->pull($cause);

        $user->causes = $causesArray;

        $user->save();

        return $this->item($user);
    }
}
