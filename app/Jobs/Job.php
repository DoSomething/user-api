<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

abstract class Job implements ShouldQueue
{
    /*
    |--------------------------------------------------------------------------
    | Queueable Jobs
    |--------------------------------------------------------------------------
    |
    | This job base class provides a central location to place any logic that
    | is shared across all of your jobs. The traits included here provide behavior
    | that we use across the board (like the "dispatch" and "onQueue" methods, or
    | Eloquent model serialization).
    |
    */

    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Delete jobs if their model(s) no longer exist. This prevents things that
     * have been deleted (either as part of automated testing or account deletions)
     * from cluttering up our failed jobs table with false negatives.
     *
     * @var bool
     */
    public $deleteWhenMissingModels = true;
}
