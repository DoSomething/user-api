<?php

namespace App\Jobs\Imports;

use App\Jobs\Job;
use App\Models\ImportFile;
use App\Models\User;
use App\Types\ImportType;
use Illuminate\Support\Facades\Storage;

class ParseEmailSubscriptions extends Job
{
    /**
     * The path to the stored csv.
     *
     * @var string
     */
    protected $path;

    /**
     * The options for this subscription import, for example
     * 'email_subscription_topic' or 'source_detail'.
     *
     * @var array
     */
    protected $options;

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
     * @param array $options
     * @param User $user
     * @return void
     */
    public function __construct(
        string $path,
        ?array $options = [],
        ?User $user = null
    ) {
        $this->path = $path;
        $this->options = $options;
        $this->user = $user;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        info('Parsing email subscriptions CSV', [
            'path' => $this->path,
            'options' => $this->options,
        ]);

        $records = read_csv($this->path);

        $importFile = ImportFile::create([
            'filepath' => $this->path,
            'import_type' => ImportType::$emailSubscription,
            'row_count' => iterator_count($records),
            'user_id' => optional($this->user)->id,
            'options' => $this->options ? json_encode($this->options) : null,
        ]);

        foreach ($records as $record) {
            ImportEmailSubscriptions::dispatch(
                $record,
                $importFile,
                $this->options,
            );
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
