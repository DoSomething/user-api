<?php

namespace App\Jobs\Imports;

use App\Models\ImportFile;
use App\Models\MutePromotionsLog;
use App\Models\User;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;

class ImportMutePromotions implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    /**
     * The Northstar user ID to mute promotions for.
     *
     * @var string
     */
    protected $userId;

    /**
     * Create a new job instance.
     *
     * @param array $record
     * @param ImportFile $importFile
     * @return void
     */
    public function __construct($record, ImportFile $importFile)
    {
        $this->userId = $record['northstar_id'];

        $this->importFile = $importFile;
    }

    /**
     * Execute the job to mute user promotions.
     */
    public function handle()
    {
        $user = User::withTrashed()->findOrFail($this->userId);

        logger('Import job handling muting promotion', ['user' => $user->id]);

        $user->mutePromotions();

        MutePromotionsLog::create([
            'import_file_id' => $this->importFile->id,
            'user_id' => $this->userId,
        ]);

        info('import.mute-promotions', [
            'user_id' => $this->userId,
            'promotions_muted_at' => $user->promotions_muted_at->toDateTimeString(),
        ]);

        $this->importFile->incrementImportCount();
    }

    /**
     * Return the parameters passed to this job.
     *
     * @return array
     */
    public function getParameters()
    {
        return [
            'import_file_id' => $this->importFile->id,
            'user_id' => $this->userId,
        ];
    }
}
