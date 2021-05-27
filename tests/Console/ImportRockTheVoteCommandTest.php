<?php

use App\Jobs\Imports\CreateRockTheVoteReport;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Bus;

class ImportRockTheVoteCommandTest extends TestCase
{
    /** @test */
    public function it_should_start_job()
    {
        Bus::fake();

        Artisan::call('northstar:rock-the-vote');

        Bus::assertDispatched(CreateRockTheVoteReport::class);
    }
}
