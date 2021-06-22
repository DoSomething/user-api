<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\ImportFile;
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
     * Read the uploaded csv and store it.
     *
     * @param \Illuminate\Http\UploadedFile $file
     * @param string $path
     * @return \League\Csv\Reader
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
     * Create import file from supplied data.
     *
     * @param \League\Csv\Reader $csv
     * @param string $path
     * @param array $importOptions
     * @return \App\Models\ImportFile
     */
    public function createImportFile($csv, $path, $importOptions)
    {
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

        return $importFile;
    }
}
