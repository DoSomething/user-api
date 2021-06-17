<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use League\Csv\Reader;
use Symfony\Component\HttpKernel\Exception\HttpException;

class FileImportController extends Controller
{
    /**
     * The type of import data.
     *
     * @var string
     */
    public $importType;

    /**
     *
     */
    public function readAndStoreCsv($file, $path)
    {
        $csv = Reader::createFromPath($file->getRealPath());

        $success = Storage::put($path, (string) $csv);

        if (!$success) {
            throw new HttpException(
                500,
                'Unable read and store file to filestystem storage.',
            );
        }

        return $csv;
    }

    /**
     *
     */
    public function createImportFile()
    {
    }
}
