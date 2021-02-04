<?php

namespace App\Console\Commands;

use App\Jobs\SendCustomerIoEmail;
use Illuminate\Console\Command;

class SendEmailCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'northstar:email {to}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Tests sending a transactional email.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        SendCustomerIoEmail::dispatch($this->argument('to'));
    }
}
