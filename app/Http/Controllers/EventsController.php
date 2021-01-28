<?php

namespace App\Http\Controllers;

use App\Http\Transformers\EventTransformer;
use App\Models\Event;
use Illuminate\Http\Request;

class EventsController extends ActivityApiController
{
    /**
     * Create a controller instance.
     */
    public function __construct()
    {
        $this->transformer = new EventTransformer();

        $this->middleware('scopes:activity');
        $this->middleware('role:admin,staff');
    }

    /**
     * Returns events.
     * GET /events.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = $this->newQuery(Event::class);

        $filters = $request->query('filter');

        $query = $this->filter($query, $filters, Event::$indexes);

        if ($filters && $filters['signup_id']) {
            $query = $query
                ->forSignup($filters['signup_id'])
                ->orderBy('created_at', 'desc');
        }

        return $this->paginatedCollection($query, $request);
    }
}
