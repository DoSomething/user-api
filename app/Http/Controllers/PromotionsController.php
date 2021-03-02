<?php

namespace App\Http\Controllers;

use App\Http\Transformers\UserTransformer;
use App\Models\User;
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
     * @param  string $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(string $id)
    {
        // Allow muting promotions for soft deleted users.
        $user = User::withTrashed()->find($id);

        $user->promotions_muted_at = now();
        $user->save();

        info('mute_promotions_request', ['id' => $user->id]);

        return $this->item($user);
    }
}
