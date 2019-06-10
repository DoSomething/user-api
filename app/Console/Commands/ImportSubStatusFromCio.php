<?php

namespace Northstar\Console\Commands;

use League\Csv\Reader;
use Northstar\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Northstar\Jobs\GetEmailSubStatusFromCustomerIo;

class ImportSubStatusFromCio extends Command
{
  /**
   * The name and signature of the console command.
   *
   * @var string
   */
  protected $signature = 'northstar:importsub {--path=}';

  /**
   * The number of jobs queued up so far.
   *
   * @var string
   */
  protected $currentCount = 0;

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = 'For each user (with the option to specify users in a given CSV), kick off a job to grab email subscription status from Customer.io.';

  /**
   * Execute the console command.
   *
   * @return void
   */
  public function handle()
  {
    // If given a CSV, only import emails from CSV.
    if ($this->argument('path')) {

    } else {
      // Grab users who have email addresses
      $query = (new User)->newQuery();
      $query = $query->where('email', 'exists', true);

      $totalCount = $query->count();

      $query->chunkById(200, function (Collection $users) use ($totalCount) {
          $users->each(function (User $user) use ($totalCount) {
              $queue = config('queue.names.low');

              dispatch(new GetEmailSubStatusFromCustomerIo($user))->onQueue($queue);
          });

          // Logging to track progress
          $this->currentCount += 200;
          $percentDone = ($this->currentCount / $totalCount) * 100;
          $this->line('northstar:importsub - '.$this->currentCount.'/'.$totalCount.' - '.$percentDone.'% done');
      });

      $this->line('northstar:importsub - Queued up a job to grab email status for each user!');
    }
  }
}
