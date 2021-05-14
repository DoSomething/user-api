<?php

namespace App\Http\Controllers\Partners;

use App\Http\Controllers\Controller;
use App\Jobs\Imports\ImportCallPowerRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CallPowerController extends Controller
{
    /**
     * Create a controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('token:X-DS-CallPower-API-Key');
    }

    /**
     * @param Request $request
     */
    public function store(Request $request)
    {
        $parameters = $request->validate([
            'mobile' => 'required',
            'callpower_campaign_id' => 'required|integer',
            'status' => 'required|string',
            'call_timestamp' => 'required|date',
            'call_duration' => 'required|integer',
            'campaign_target_name' => 'required|string',
            'campaign_target_title' => 'required|string',
            'campaign_target_district' => 'nullable|string',
            'callpower_campaign_name' => 'required|string',
            'number_dialed_into' => 'required',
        ]);

        ImportCallPowerRecord::dispatch($parameters);

        return $this->respond('Received CallPower payload.');
    }
}
