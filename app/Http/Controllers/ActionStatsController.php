<?php

namespace App\Http\Controllers;

use App\Http\Transformers\ActionStatTransformer;
use App\Models\ActionStat;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class ActionStatsController extends ActivityApiController
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
        $this->transformer = new ActionStatTransformer();
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = $this->newQuery(ActionStat::class);

        $filters = $request->query('filter');
        $query = $this->filter($query, $filters, ActionStat::$indexes);

        if (Arr::has($filters, 'group_type_id')) {
            $query = $query->inGroupTypeId($filters['group_type_id']);
        }

        // Allow ordering results:
        $orderBy = $request->query('orderBy');
        $query = $this->orderBy($query, $orderBy, ActionStat::$sortable);

        if ($cursor = Arr::get($request->query('cursor'), 'after')) {
            $query->whereAfterCursor($cursor);

            // Using 'cursor' implies cursor pagination:
            $this->useCursorPagination = true;
        }

        return $this->paginatedCollection($query, $request);
    }
}
