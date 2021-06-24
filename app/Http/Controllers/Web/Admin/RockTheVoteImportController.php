<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\Imports\FetchRockTheVoteReport;
use App\Models\ImportFile;
use App\Models\RockTheVoteLog;
use App\Models\RockTheVoteReport;
use App\Types\ImportType;
use Carbon\Carbon;
use Illuminate\Http\Request;

class RockTheVoteImportController extends Controller
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

        $this->importType = ImportType::$rockTheVote;
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

        return view('admin.imports.rock-the-vote.index', [
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

        return view('admin.imports.rock-the-vote.create', [
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
        $request->validate([
            'since' => 'required|date_format:Y-m-d H:i:s',
            'before' => 'required|date_format:Y-m-d H:i:s',
        ]);

        $report = RockTheVoteReport::createViaApi(
            new Carbon($request->input('since')),
            new Carbon($request->input('before')),
        );

        FetchRockTheVoteReport::dispatch($report, auth()->user());

        return redirect()
            ->route('admin.imports.rock-the-vote.create')
            ->with('status', 'Requested report from Rock The Vote...');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\ImportFile $importFile
     * @return \Illuminate\Http\Response
     */
    public function show(ImportFile $importFile)
    {
        $importedItems = RockTheVoteLog::where(
            'import_file_id',
            $importFile->id,
        )->paginate(100);

        return view('admin.imports.rock-the-vote.show', [
            'importFile' => $importFile,
            'importedItems' => $importedItems,
        ]);
    }
}
