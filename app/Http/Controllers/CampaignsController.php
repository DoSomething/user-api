<?php

namespace App\Http\Controllers;

use App\Http\Transformers\CampaignTransformer;
use App\Models\Campaign;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class CampaignsController extends ActivityApiController
{
    /**
     * @var App\Http\Transformers\CampaignTransformer;
     */
    protected $transformer;

    /**
     * Create a controller instance.
     */
    public function __construct()
    {
        $this->transformer = new CampaignTransformer();

        $this->rules = [
            'contentful_campaign_id' => ['nullable', 'string', 'max:255'],
            'group_type_id' => ['nullable', 'integer'],
        ];
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = $this->newQuery(Campaign::class);

        $filters = $request->query('filter');
        $query = $this->filter($query, $filters, Campaign::$indexes);

        // Apply scope for the "computed" is_open field:
        if (isset($filters['is_open'])) {
            // We can use this to find only open campaigns with a truthy filter
            // applied, e.g. `?filter[is_open]=true`, or closed campaigns with
            // a falsy filter, e.g. `?filter[is_open]=false`.
            if (filter_var($filters['is_open'], FILTER_VALIDATE_BOOLEAN)) {
                $query->whereOpen();
            } else {
                $query->whereClosed();
            }
        }

        // Apply scope for the has_website filter:
        if (isset($filters['has_website'])) {
            if (filter_var($filters['has_website'], FILTER_VALIDATE_BOOLEAN)) {
                $query->whereHasWebsite();
            } else {
                $query->whereDoesNotHaveWebsite();
            }
        }

        // Apply scope for campaigns including specified causes:
        if (isset($filters['cause'])) {
            $query->withCauses($filters['cause']);
        }

        // Experimental: Allow paginating by cursor (e.g. `?cursor[after]=OTAxNg==`):
        if ($cursor = Arr::get($request->query('cursor'), 'after')) {
            $query->whereAfterCursor($cursor);

            // Using 'cursor' implies cursor pagination:
            $this->useCursorPagination = true;
        }

        // Allow ordering results:
        $orderBy = $request->query('orderBy');
        $query = $this->orderBy($query, $orderBy, Campaign::$sortable);

        return $this->paginatedCollection($query, $request);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Campaign  $campaign
     * @return \Illuminate\Http\Response
     */
    public function show(Campaign $campaign, Request $request)
    {
        return $this->item($campaign);
    }

    /**
     * Updates a specific campaign
     * PATCH /api/campaigns/:id.
     *
     * @param Request $request
     * @param  \App\Models\Campaign  $campaign
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, Campaign $campaign)
    {
        $this->authorize('update', $campaign);

        $values = $this->validate($request, [
            'contentful_campaign_id' => ['nullable', 'string', 'max:255'],
            'group_type_id' => ['nullable', 'integer'],
        ]);

        $campaign->update($values);

        return $this->item($campaign);
    }
}
