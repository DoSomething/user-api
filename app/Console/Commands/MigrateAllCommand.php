<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class MigrateAllCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migrate:all {--seed : Run database seeders.} {--fresh : Drop all tables & re-run migrations.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run MySQL & MongoDB database migrations';

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
        $migrateCommand = $this->option('fresh') ? 'migrate:fresh' : 'migrate';

        $this->line('Running MongoDB database migrations...');
        $this->call($migrateCommand, [
            '--database' => 'mongodb',
            '--path' => 'database/migrations-mongodb/',
        ]);

        $this->line('');

        $this->line('Running MySQL database migrations...');
        $this->call($migrateCommand, [
            '--database' => 'mysql',
            '--path' => 'database/migrations',
        ]);

        if ($this->option('seed')) {
            $this->line('');

            $this->line('Running database seeders...');
            $this->call('db:seed');
        }
    }
}
