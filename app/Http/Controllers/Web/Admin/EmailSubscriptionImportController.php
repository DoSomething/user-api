<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Web\Admin\FileImportController;
use App\Jobs\Imports\ImportEmailSubscriptions;
use App\Models\ImportFile;
use App\Types\ImportType;
use Carbon\Carbon;
use Illuminate\Http\Request;

class EmailSubscriptionImportController extends FileImportController
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

        $this->importType = ImportType::$emailSubscription;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $imports = ImportFile::where('import_type', $this->importType)
            ->orderBy('id', 'desc')
            ->paginate(15);

        return view('admin.imports.email-subscriptions.index', [
            'imports' => $imports,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $config = ImportType::getConfig($this->importType);

        return view('admin.imports.email-subscriptions.create', [
            'config' => $config,
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
        $rules = [
            'source-detail' => 'required',
            'topic' => 'required',
            'upload-file' => 'required|mimes:csv,txt',
        ];

        $request->validate($rules);

        $upload = $request->file('upload-file');

        // Save original file name to reference in the Admin UI.
        $importOptions = [
            'email_subscription_topic' => $request->input('topic'),
            'name' => $upload->getClientOriginalName(),
            'source_detail' => $request->input('source-detail'),
        ];

        $path =
            'temporary/email-subscriptions-importer-' .
            Carbon::now()->timestamp .
            '.csv';

        $csv = $this->readAndStoreCsv($upload, $path);

        $importFile = $this->createImportFile($csv, $path, $importOptions);

        foreach ($csv->getRecords() as $record) {
            ImportEmailSubscriptions::dispatch(
                $record,
                $importFile,
                $importOptions,
            )
                ->delay(now()->addSeconds(3))
                ->onQueue(config('queue.names.high'));
        }

        return redirect('/admin/imports/email-subscriptions');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\ImportFile $importFile
     * @return \Illuminate\Http\Response
     */
    public function show(ImportFile $importFile)
    {
        return view('admin.imports.email-subscriptions.show', [
            'importFile' => $importFile,
        ]);
    }
}
