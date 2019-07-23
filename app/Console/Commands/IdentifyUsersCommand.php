<?php

namespace Northstar\Console\Commands;

use League\Csv\Reader;
use League\Csv\Writer;
use Northstar\Auth\Registrar;
use Illuminate\Console\Command;

class IdentifyUsersCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'northstar:id {column=email}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get IDs, given a CSV with column that uniquely identifies users.';

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
    public function handle(Registrar $registrar)
    {
        $stdin = file_get_contents('php://stdin');
        $csv = Reader::createFromString($stdin);
        $csv->setHeaderOffset(0);

        $column = $this->argument('column');
        $output = Writer::createFromString();

        // Set headers on outputted CSV.
        $output->insertOne(array_merge(['id'], $csv->getHeader()));

        foreach ($csv->getRecords() as $record) {
            $user = $registrar->resolve([$column => $record[$column]]);

            $output->insertOne(array_merge([
                'id' => $user ? $user->id : '-',
            ], $record));
        }

        $this->line($output->getContent());
    }
}
