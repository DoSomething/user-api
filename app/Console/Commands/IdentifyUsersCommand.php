<?php

namespace Northstar\Console\Commands;

use Illuminate\Console\Command;
use League\Csv\Reader;
use League\Csv\Writer;
use Northstar\Auth\Registrar;

class IdentifyUsersCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'northstar:id {input=php://stdin} {output=php://stdout} {--csv_column=email} {--database_column=email}';

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
        $input = file_get_contents($this->argument('input'));
        $csv = Reader::createFromString($input);
        $csv->setHeaderOffset(0);

        $csvColumn = $this->option('csv_column');
        $output = Writer::createFromString();

        // Set headers on outputted CSV.
        $output->insertOne(array_merge(['id'], $csv->getHeader()));

        foreach ($csv->getRecords() as $record) {
            $databaseColumn = $this->option('database_column') ?: $csvColumn;
            $user = $registrar->resolve([
                $databaseColumn => $record[$csvColumn],
            ]);

            $output->insertOne(
                array_merge(
                    [
                        'id' => $user ? $user->id : 'N/A',
                    ],
                    $record,
                ),
            );
        }

        // Write the combined CSV to the specified output.
        file_put_contents($this->argument('output'), $output->getContent());
    }
}
