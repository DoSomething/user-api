<?php

namespace App\Http\Controllers;

use App\Http\Transformers\UserTransformer;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

class PromotionsController extends Controller
{
    /**
     * @var UserTransformer
     */
    protected $transformer;

    /**
     * Make a new PromotionsController, inject dependencies,
     * and set middleware for this controller's methods.
     *
     * @param UserTransformer $transformer
     */
    public function __construct(UserTransformer $transformer)
    {
        $this->transformer = $transformer;

        $this->middleware('role:admin,staff');
    }

    /**
     * Mute promotions for the specified resource.
     *
     * @TODO: this existing as a destroy method seems odd, since it is not directly
     * deleting a resource but setting a property on the User model. Contextually,
     * this will ultimately result in the deletion of the user from CustomerIO, but
     * could be a bit clearer.
     *
     * @param  string $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(string $id)
    {
        // Allow muting promotions for soft deleted users.
        $user = User::withTrashed()->find($id);

        if (!$user) {
            throw new ModelNotFoundException();
        }

        $user->mutePromotions();

        info('mute_promotions_request', ['id' => $user->id]);

        return $this->item($user);
    }
}
