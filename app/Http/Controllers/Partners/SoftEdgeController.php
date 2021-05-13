<?php

namespace App\Http\Controllers\Partners;

use App\Http\Controllers\Controller;
use App\Jobs\Imports\ImportSoftEdgeRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SoftEdgeController extends Controller
{
    /**
     * Create a controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('token:X-DS-SoftEdge-API-Key');
    }

    /**
     * @param Request $request
     */
    public function store(Request $request)
    {
        $parameters = $request->validate([
            'northstar_id' => 'required',
            'action_id' => 'required|integer',
            'email_timestamp' => 'required|date',
            'campaign_target_name' => 'required|string',
            'campaign_target_title' => 'nullable|string',
            'campaign_target_district' => 'nullable|string',
        ]);

        ImportSoftEdgeRecord::dispatch($parameters);

        return $this->respond('Received SoftEdge payload.');
    }
}
