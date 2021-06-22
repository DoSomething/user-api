<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\Imports\ParseEmailSubscriptions;
use App\Models\ImportFile;
use App\Types\ImportType;
use Illuminate\Http\Request;

class EmailSubscriptionImportController extends Controller
{
    /**
     * The type of import data.
     *
     * @var string
     */
    public $importType;

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

        $options = [
            'email_subscription_topic' => $request->input('topic'),
            // Save original file name to reference in the Admin UI.
            'name' => $upload->getClientOriginalName(),
            'source_detail' => $request->input('source-detail'),
        ];

        $path = store_csv($upload, $this->importType);

        ParseEmailSubscriptions::dispatch($path, $options, auth()->user());

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
