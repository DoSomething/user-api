<?php

namespace App\Http\Controllers;

use App\Http\Transformers\ActionTransformer;
use App\Models\Action;
use Illuminate\Http\Request;

class ActionsController extends ActivityApiController
{
    /**
     * @var App\Http\Transformers\ActionTransformer;
     */
    protected $transformer;

    /**
     * Create a controller instance.
     */
    public function __construct()
    {
        $this->transformer = new ActionTransformer();
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = $this->newQuery(Action::class);

        $filters = $request->query('filter');
        $query = $this->filter($query, $filters, Action::$indexes);

        return $this->paginatedCollection($query, $request);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Action  $action
     * @return \Illuminate\Http\Response
     */
    public function show(Action $action)
    {
        return $this->item($action);
    }
}
