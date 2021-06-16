<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\Imports\ImportMutePromotions;
use App\Models\ImportFile;
use App\Models\MutePromotionsLog;
use App\Types\ImportType;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use League\Csv\Reader;
use Symfony\Component\HttpKernel\Exception\HttpException;

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
        $importOptions = [];

        $rules = [
            'upload-file' => 'required|mimes:csv,txt',
        ];

        $request->validate($rules);

        $upload = $request->file('upload-file');

        // Save original file name to reference in the Admin UI.
        $importOptions['name'] = $upload->getClientOriginalName();

        // Push file to storage.
        $path =
            'temporary/mute-promotions-importer-' .
            Carbon::now()->timestamp .
            '.csv';

        $csv = Reader::createFromPath($upload->getRealPath());

        $success = Storage::put($path, (string) $csv);

        if (!$success) {
            throw new HttpException(
                500,
                'Unable read and store file to filestystem storage.',
            );
        }

        $queue = config('queue.names.high');

        $user = auth()->user();

        $csv->setHeaderOffset(0);

        $importFile = new ImportFile([
            'filepath' => $path,
            'import_type' => $this->importType,
            'row_count' => count($csv),
            'user_id' => $user->id,
            'options' => $importOptions ? json_encode($importOptions) : null,
        ]);

        $importFile->save();

        $records = $csv->getRecords();

        foreach ($records as $record) {
            ImportMutePromotions::dispatch(
                ['northstar_id' => $record['northstar_id']],
                $importFile,
            )
                ->delay(now()->addSeconds(3))
                ->onQueue($queue);
        }

        return redirect('/admin/imports/mute-promotions');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $importFile = ImportFile::findOrFail($id);

        $importedItems = MutePromotionsLog::where(
            'import_file_id',
            $id,
        )->paginate(100);

        return view('admin.imports.mute-promotions.show', [
            'importFile' => $importFile,
            'importedItems' => $importedItems,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
