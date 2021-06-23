<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\Imports\ParseMutePromotions;
use App\Models\ImportFile;
use App\Models\MutePromotionsLog;
use App\Types\ImportType;
use Illuminate\Http\Request;

class MutePromotionImportController extends Controller
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

        $this->importType = ImportType::$mutePromotions;
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

        return view('admin.imports.mute-promotions.index', [
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

        return view('admin.imports.mute-promotions.create', [
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
            'upload-file' => 'required|mimes:csv,txt',
        ]);

        $upload = $request->file('upload-file');

        // Save original file name to reference in the Admin UI.
        $options = ['name' => $upload->getClientOriginalName()];

        $path = store_csv($upload, $this->importType);

        ParseMutePromotions::dispatch($path, $options, auth()->user());

        return redirect('/admin/imports/mute-promotions');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\ImportFile  $importFile
     * @return \Illuminate\Http\Response
     */
    public function show(ImportFile $importFile)
    {
        $importedItems = MutePromotionsLog::where(
            'import_file_id',
            $importFile->id,
        )->paginate(100);

        return view('admin.imports.mute-promotions.show', [
            'importFile' => $importFile,
            'importedItems' => $importedItems,
        ]);
    }
}
