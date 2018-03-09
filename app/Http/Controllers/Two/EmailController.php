<?php

namespace Northstar\Http\Controllers\Two;

use Northstar\Auth\Registrar;
use Northstar\Http\Transformers\Two\UserTransformer;
use Northstar\Http\Controllers\Controller;

class EmailController extends Controller
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
     * Make a new EmailController, inject dependencies,
     * and set middleware for this controller's methods.
     *
     * @param Registrar $registrar
     * @param UserTransformer $transformer
     */
    public function __construct(Registrar $registrar, UserTransformer $transformer)
    {
        $this->registrar = $registrar;
        $this->transformer = $transformer;
    }



    /**
     * Display the specified resource.
     * GET /email/:id
     *
     * @param string $id - the actual value to search for
     *
     * @return \Illuminate\Http\Response
     * @throws NotFoundHttpException
     */
    public function show($id)
    {
        $user = $this->registrar->resolveOrFail(['email' => $id]);

        return $this->item($user);
    }

}
