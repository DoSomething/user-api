<?php

namespace App\Console\Commands;

use App\Models\Group;
use App\Models\GroupType;
use Exception;
use Illuminate\Console\Command;
use League\Csv\Reader;

class ImportNasspGroupsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rogue:nassp-groups-import {path}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create National Association of Secondary School Principals group types and groups from a CSV.';

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
     * Returns a trimmed CSV value, or null if equal to the string "NULL".
     *
     * @param string
     * @return string
     */
    public function sanitize($value)
    {
        $result = trim($value);

        return $result == 'NULL' ? null : $result;
    }

    /**
     * Execute the console command.
     *
     * @return bool
     */
    public function handle()
    {
        // Sample CSV path: https://gist.githubusercontent.com/aaronschachter/cb7c2c75f15d67abea548bb586370dd7/raw/3cb12bb44e8d7b30436588c36c3a6280a501d1f4/nassp.csv

        $path = $this->argument('path');

        info('rogue:nassp-groups-import: Loading csv from ' . $path);

        // Make a local copy of the CSV.
        $temp = tempnam(sys_get_temp_dir(), 'command_csv');

        file_put_contents($temp, fopen($this->argument('path'), 'r'));

        // Load CSV contents.
        $csv = Reader::createFromPath($temp, 'r');

        $csv->setHeaderOffset(0);

        $numImported = 0;
        $numFailed = 0;
        $numSkipped = 0;

        $nhsGroupType = GroupType::firstOrCreate([
            'name' => 'National Honor Society',
            'filter_by_location' => true,
        ]);
        $njhsGroupType = GroupType::firstOrCreate([
            'name' => 'National Junior Honor Society',
            'filter_by_location' => true,
        ]);
        $nascGroupType = GroupType::firstOrCreate([
            'name' => 'National Student Council',
            'filter_by_location' => true,
        ]);
        $nasspGroupType = GroupType::firstOrCreate([
            'name' => 'National Association of Secondary School Principals',
            'filter_by_location' => true,
        ]);

        info('rogue:nassp-groups-import: Beginning import...');

        foreach ($csv->getRecords() as $record) {
            $groupTypeId = null;
            $name = trim($record['name']);

            if (trim($record['nhs']) == 'NHS') {
                $groupTypeId = $nhsGroupType->id;
            } elseif (trim($record['njhs']) == 'NJHS') {
                $groupTypeId = $njhsGroupType->id;
            } elseif (trim($record['nasc']) == 'NASC') {
                $groupTypeId = $nascGroupType->id;
            }

            try {
                $city = $this->sanitize($record['city']);
                $location = 'US-' . $this->sanitize($record['state']);
                $schoolId = $this->sanitize($record['universalid']);

                if ($groupTypeId) {
                    $group = Group::firstOrCreate([
                        'group_type_id' => $groupTypeId,
                        'name' => $name,
                        'city' => $city,
                        'location' => $location,
                        'school_id' => $schoolId,
                    ]);

                    $numImported++;

                    info('rogue:nassp-groups-import: Imported group', [
                        'id' => $group->id,
                        'group_type_id' => $groupTypeId,
                        'name' => $group->name,
                    ]);
                }

                $nasspGroup = Group::firstOrCreate([
                    'group_type_id' => $nasspGroupType->id,
                    'name' => $name,
                    'city' => $city,
                    'location' => $location,
                    'school_id' => $schoolId,
                ]);

                $numImported++;

                info('rogue:nassp-groups-import: Imported group', [
                    'id' => $nasspGroup->id,
                    'group_type_id' => $nasspGroupType->id,
                    'name' => $nasspGroup->name,
                ]);
            } catch (Exception $e) {
                $numFailed++;

                info(
                    'rogue:nassp-groups-import: Error importing group with ' .
                        $name .
                        ':' .
                        $e->getMessage(),
                );
            }
        }

        info(
            'rogue:nassp-groups-import: Import completed with ' .
                $numImported .
                ' imported, ' .
                $numSkipped .
                ' skipped, and ' .
                $numFailed .
                ' failed.',
        );

        return 0;
    }
}
