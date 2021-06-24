<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\Imports\ImportRockTheVoteRecord;
use App\Models\ImportFile;
use App\Types\ImportType;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RockTheVoteTestController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:web');
        $this->middleware('role:staff,admin');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $config = ImportType::getConfig(ImportType::$rockTheVote);

        $user = auth()->user();

        $data = [
            'addr_street1' => $user->addr_street1,
            'addr_street2' => $user->addr_street2,
            'addr_city' => $user->addr_city,
            'addr_zip' => $user->addr_zip,
            'email' => $user->email,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'phone' => $user->mobile,
            'tracking_source' => 'source:test,source_details:ChompyUI',
            'started_registration' => Carbon::now()->format('Y-m-d H:i:s O'),
        ];

        return view('admin.imports.rock-the-vote.test', [
            'config' => $config,
            'data' => $data,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Format form input into a Rock The Vote CSV payload:
        $row = [
            'Email address' => $request->input('email'),
            'Finish with State' => $request->input('finish_with_state') ?: 'No',
            'First name' => $request->input('first_name'),
            'Home address' => $request->input('addr_street1'),
            'Home city' => $request->input('addr_city'),
            'Home unit' => $request->input('addr_street2'),
            'Home zip code' => $request->input('addr_zip'),
            'Last name' => $request->input('last_name'),
            'Opt-in to Partner SMS/robocall' =>
                $request->input('sms_opt_in') ?: 'No',
            'Opt-in to Partner email?' =>
                $request->input('email_opt_in') ?: 'No',
            'Phone' => $request->input('phone'),
            'Pre-Registered' => $request->input('pre_registered') ?: 'No',
            'Started registration' => $request->input('started_registration'),
            'Status' => $request->input('status'),
            'Tracking Source' => $request->input('tracking_source'),
        ];

        $importFile = ImportFile::create([
            'user_id' => Auth::id(),
            'row_count' => 1,
            'filepath' => 'n/a',
            'import_type' => ImportType::$rockTheVote,
        ]);

        $response = ImportRockTheVoteRecord::dispatchNow($row, $importFile);

        $result = array_merge(
            [
                'import' => ['id' => $importFile->id],
            ],
            $response,
        );

        return redirect('admin/imports/rock-the-vote/create?source=test')
            ->withInput($request->input())
            ->with('status', $result);
    }
}
