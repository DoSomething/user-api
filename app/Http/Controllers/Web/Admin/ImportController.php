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
}
