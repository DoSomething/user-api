<?php

namespace App\Http\Controllers;

use App\Http\Transformers\ClubTransformer;
use App\Models\Club;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class ClubsController extends ActivityApiController
{
    /**
     * @var App\Http\Transformers\ClubTransformer;
     */
    protected $transformer;

    /**
     * Create a controller instance.
     */
    public function __construct()
    {
        $this->transformer = new ClubTransformer();
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = $this->newQuery(Club::class);

        $filters = $request->query('filter');
        $query = $this->filter($query, $filters, Club::$indexes);

        if (isset($filters['name'])) {
            $query->where('name', 'LIKE', '%' . $filters['name'] . '%');
        }

        if ($cursor = Arr::get($request->query('cursor'), 'after')) {
            $query->whereAfterCursor($cursor);
            $this->useCursorPagination = true;
        }

        return $this->paginatedCollection($query, $request);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Club  $club
     * @return \Illuminate\Http\Response
     */
    public function show(Club $club)
    {
        return $this->item($club);
    }
}
