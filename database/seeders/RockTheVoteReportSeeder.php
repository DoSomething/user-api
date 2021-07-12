<?php

namespace Database\Seeders;

use App\Models\RockTheVoteReport;
use Illuminate\Database\Seeder;

class RockTheVoteReportSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $reportCount = RockTheVoteReport::count();

        // Execute for loop to create unique ID's (which are generated externally by RTV API).
        for ($i = $reportCount + 1; $i < $reportCount + 10; $i++) {
            factory(RockTheVoteReport::class)->create([
                'id' => $i,
                'status' => $i % 2 === 0 ? 'completed' : 'failed',
            ]);
        }
    }
}
