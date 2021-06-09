<?php

namespace App\Jobs\Imports;

use App\Jobs\Job;
use App\Models\ImportFile;
use App\Models\User;
use App\Types\ImportType;
use Illuminate\Support\Facades\Storage;

class ParseRockTheVoteReport extends Job
{
    /**
     * The path to the stored csv.
     *
     * @var string
     */
    protected $path;

    /**
     * Optionally, the user that triggered this.
     *
     * @var User
     */
    protected $user;

    /**
     * Create a new job instance.
     *
     * @param string $path
     * @param User $user
     * @return void
     */
    public function __construct(string $path, ?User $user = null)
    {
        $this->path = $path;
        $this->user = $user;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        info('Parsing Rock The Vote CSV', ['path' => $this->path]);

        $records = read_csv($this->path);

        $importFile = ImportFile::create([
            'filepath' => $this->path,
            'import_type' => ImportType::$rockTheVote,
            'row_count' => iterator_count($records),
            'user_id' => optional($this->user)->id,
        ]);

        foreach ($records as $record) {
            ImportRockTheVoteRecord::dispatch($record, $importFile);
        }

        // Now that we've chomped, delete the import file.
        Storage::delete($this->path);
    }

    /**
     * Returns the parameters passed to this job.
     *
     * @return array
     */
    public function getParameters()
    {
        return [];
    }
}
