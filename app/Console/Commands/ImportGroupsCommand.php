<?php

namespace App\Console\Commands;

use App\Models\Group;
use App\Models\GroupType;
use Exception;
use Illuminate\Console\Command;
use League\Csv\Reader;

class ImportGroupsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rogue:groups-import {input=php://stdin} {--name=} {--filterByLocation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates or updates a group type and its groups from a CSV.';

    /**
     * The ID of the group type that will or does belong to the name option passed.
     *
     * @var int
     */
    protected $groupTypeId = null;

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
     * Helper to log info with command name prefix and the group type ID we're importing.
     *
     * @param array $record
     * @param string $key
     * @return string
     */
    private function logInfo($message, $data = [])
    {
        // Append the group type ID to the data if we have it set.
        $data = $this->groupTypeId
            ? array_merge(['group_type_id' => $this->groupTypeId], $data)
            : $data;

        info('rogue:groups-import: ' . $message, $data ?: []);
    }

    /**
     * Trims the given record value for given key if exists.
     *
     * @param array $record
     * @param string $key
     * @return string
     */
    private function sanitizeValue($record, $key)
    {
        if (!isset($record[$key])) {
            return null;
        }

        return trim($record[$key]);
    }

    /**
     * Execute the console command.
     *
     * @return bool
     */
    public function handle()
    {
        $groupTypeName = $this->option('name');

        if (!$groupTypeName) {
            $this->logInfo('Please provide a name option.');

            return 0;
        }

        $input = file_get_contents($this->argument('input'));
        $csv = Reader::createFromString($input);

        $csv->setHeaderOffset(0);

        $numImported = 0;
        $numFailed = 0;
        $numSkipped = 0;

        $groupType = GroupType::firstOrCreate([
            'name' => $groupTypeName,
            'filter_by_location' => $this->option('filterByLocation'),
        ]);

        $this->groupTypeId = $groupType->id;

        $this->logInfo('Beginning import');

        foreach ($csv->getRecords() as $record) {
            $groupName = $this->sanitizeValue($record, 'name');
            $groupSchoolId = $this->sanitizeValue($record, 'universal_id');
            $groupCity = $this->sanitizeValue($record, 'city');

            if (!$groupName) {
                $numSkipped++;

                $this->logInfo('Skipping row without group name', [
                    'school_id' => $groupSchoolId,
                ]);

                continue;
            }

            try {
                $group = Group::firstOrCreate([
                    'group_type_id' => $this->groupTypeId,
                    'name' => $groupName,
                    // Convert any uppercase city names to Title Case.
                    'city' => isset($groupCity)
                        ? ucwords(strtolower($groupCity))
                        : null,
                    // Convert US State abbreviation to ISO 3166 format.
                    'location' => isset($record['state'])
                        ? 'US-' . trim($record['state'])
                        : null,
                    'school_id' => $groupSchoolId,
                ]);

                $numImported++;

                $this->logInfo('Imported group', [
                    'id' => $group->id,
                    'name' => $group->name,
                ]);
            } catch (Exception $e) {
                $numFailed++;

                $this->logInfo('Error: ' . $e->getMessage(), [
                    'name' => $groupName,
                ]);
            }
        }

        $this->logInfo('Import complete', [
            'imported' => $numImported,
            'skipped' => $numSkipped,
            'failed' => $numFailed,
        ]);

        return 0;
    }
}
