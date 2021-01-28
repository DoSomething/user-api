<?php

namespace App\Http\Controllers;

use App\Http\Transformers\GroupTypeTransformer;
use App\Models\GroupType;
use Illuminate\Http\Request;

class GroupTypesController extends ActivityApiController
{
    /**
     * @var App\Http\Transformers\GroupTypeTransformer;
     */
    protected $transformer;

    /**
     * Create a controller instance.
     */
    public function __construct()
    {
        $this->transformer = new GroupTypeTransformer();
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = $this->newQuery(GroupType::class);

        return $this->paginatedCollection($query, $request);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\GroupType  $groupType
     * @return \Illuminate\Http\Response
     */
    public function show(GroupType $groupType)
    {
        return $this->item($groupType);
    }
}
