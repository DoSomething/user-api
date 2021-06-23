<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\ImportFile;
// use App\Jobs\Imports\ImportFileRecords;
use Illuminate\Http\Request;

class ImportController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:web');
        $this->middleware('role:admin,staff');
    }

    /**
     * Display a listing of all import file types.
     */
    public function __invoke()
    {
        $imports = ImportFile::paginate(15);

        return view('admin.imports.index', [
            'importFiles' => $imports,
        ]);
    }

    // @TODO: remove; left for reference
    // /**
    //  * Import the uploaded file.
    //  *
    //  * @param Request $request
    //  * @param string $importType
    //  */
    // public function upload(Request $request, $importType)
    // {
    //     // @TODO: add status indicator to other controller redirects on store().
    //     return redirect('import/' . $importType)->with(
    //         'status',
    //         'Queued ' . $path . ' for import.',
    //     );
    // }

    // @TODO: remove; left for reference
    // /**
    //  * Imports an create request.
    //  *
    //  * @param Request $request
    //  * @param string $importType
    //  */
    // public function store(Request $request, $importType)
    // {
    //     $result = [];

    //     if ($importType === ImportType::$rockTheVote) {
    //         $row = [
    //             'Email address' => $request->input('email'),
    //             'Finish with State' =>
    //                 $request->input('finish_with_state') ?: 'No',
    //             'First name' => $request->input('first_name'),
    //             'Home address' => $request->input('addr_street1'),
    //             'Home city' => $request->input('addr_city'),
    //             'Home unit' => $request->input('addr_street2'),
    //             'Home zip code' => $request->input('addr_zip'),
    //             'Last name' => $request->input('last_name'),
    //             'Opt-in to Partner SMS/robocall' =>
    //                 $request->input('sms_opt_in') ?: 'No',
    //             'Opt-in to Partner email?' =>
    //                 $request->input('email_opt_in') ?: 'No',
    //             'Phone' => $request->input('phone'),
    //             'Pre-Registered' => $request->input('pre_registered') ?: 'No',
    //             'Started registration' => $request->input(
    //                 'started_registration',
    //             ),
    //             'Status' => $request->input('status'),
    //             'Tracking Source' => $request->input('tracking_source'),
    //         ];

    //         $importFile = new ImportFile();

    //         $importFile->user_id = Auth::id();
    //         $importFile->row_count = 1;
    //         $importFile->filepath = 'n/a';
    //         $importFile->import_type = $importType;
    //         $importFile->save();

    //         $result = array_merge(
    //             [
    //                 'import' => ['id' => $importFile->id],
    //             ],
    //             ImportRockTheVoteRecord::dispatchNow($row, $importFile),
    //         );
    //     }

    //     return redirect('import/' . $importType . '?source=test')
    //         ->withInput($request->input())
    //         ->with('status', $result);
    // }
}
