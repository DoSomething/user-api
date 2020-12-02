<?php

namespace App\Http\Controllers;

use App\Http\Transformers\UserTransformer;
use App\Models\User;

class MobileController extends Controller
{
    /**
     * @var UserTransformer
     */
    protected $transformer;

    /**
     * Make a new MobileController, inject dependencies,
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
     * Display the specified resource.
     *
     * @param object $user
     *
     * @return \Illuminate\Http\Response
     * @throws NotFoundHttpException
     */
    public function show(User $user)
    {
        return $this->item($user);
    }
}
