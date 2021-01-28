<?php

namespace App\Console\Commands;

use App\Models\Group;
use App\Models\GroupType;
use Exception;
use Illuminate\Console\Command;
use League\Csv\Reader;

class ImportMfolGroupsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rogue:mfol-groups-import {path}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create March For Our Lives groups from a CSV.';

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
        $path = $this->argument('path');

        info('rogue:mfol-groups-import: Loading csv from ' . $path);

        // Make a local copy of the CSV.
        $temp = tempnam(sys_get_temp_dir(), 'command_csv');

        file_put_contents($temp, fopen($this->argument('path'), 'r'));

        // Load CSV contents.
        $csv = Reader::createFromPath($temp, 'r');

        $csv->setHeaderOffset(0);

        $numImported = 0;
        $numFailed = 0;

        $groupType = GroupType::firstOrCreate([
            'name' => 'March For Our Lives',
        ]);

        info(
            'rogue:mfol-groups-import: Beginning import for group type id ' .
                $groupType->id .
                '.',
        );

        foreach ($csv->getRecords() as $record) {
            $name = $record['Chapter'];

            try {
                $group = Group::firstOrCreate([
                    'group_type_id' => $groupType->id,
                    'name' => $name,
                ]);

                $numImported++;

                info('Imported group', [
                    'id' => $group->id,
                    'name' => $group->name,
                ]);
            } catch (Exception $e) {
                $numFailed++;

                info(
                    'Error importing group with ' .
                        $name .
                        ':' .
                        $e->getMessage(),
                );
            }
        }

        info(
            'rogue:mfol-groups-import: Import completed with ' .
                $numImported .
                ' imported and ' .
                $numFailed .
                ' failed.',
        );
    }
}
