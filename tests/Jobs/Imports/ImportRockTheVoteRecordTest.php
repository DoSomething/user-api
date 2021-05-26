<?php

use App\Imports\RockTheVoteRecord;
use App\Jobs\Imports\ImportRockTheVoteRecord;
use App\Models\Action;
use App\Models\ImportFile;
use App\Models\RockTheVoteLog;
use App\Models\User;
use Carbon\Carbon;

class ImportRockTheVoteRecordTest extends TestCase
{
    /**
     * Make a fake data payload for a Rock The Vote report.
     *
     * @param array $data
     * @return array
     */
    public function makeFakeReportPayload($data = [])
    {
        return array_merge(
            [
                'Home address' => $this->faker->streetAddress,
                'Home unit' => $this->faker->randomDigit,
                'Home city' => $this->faker->city,
                'Home state' => $this->faker->state,
                'Home zip code' => $this->faker->postcode,
                'Email address' => $this->faker->email,
                'First name' => $this->faker->firstName,
                'Last name' => $this->faker->lastName,
                'Phone' => null,
                'Finish with State' => 'Yes',
                'Pre-Registered' => 'No',
                'Started registration' => $this->daysAgoInRockTheVoteFormat(),
                'Status' => 'Step 2',
                'Tracking Source' => 'ads',
                'Opt-in to Partner email?' => 'Yes',
                'Opt-in to Partner SMS/robocall' => 'No',
            ],
            $data,
        );
    }

    /**
     * Make a fake data payload for a Rock The Vote report with provided user.
     *
     * @param \App\Models\User $user
     * @param array $data
     * @return array
     */
    public function makeFakeReportPayloadForSpecificUser($user, $data = [])
    {
        return array_merge(
            [
                'Home address' => $user->addr_street1,
                'Home unit' => $user->addr_street2,
                'Home city' => $user->addr_city,
                'Home state' => $user->addr_state,
                'Home zip code' => $user->addr_zip,
                'Email address' => $user->email,
                'First name' => $user->first_name,
                'Last name' => $user->last_name,
                'Phone' => null,
                'Finish with State' => 'Yes',
                'Pre-Registered' => 'No',
                'Started registration' => $this->daysAgoInRockTheVoteFormat(),
                'Status' => 'Step 2',
                'Tracking Source' => 'ads',
                'Opt-in to Partner email?' => 'Yes',
                'Opt-in to Partner SMS/robocall' => 'No',
            ],
            $data,
        );
    }

    /**
     * Make a fake voter registration post action.
     *
     * @return \App\Models\Action
     */
    public function makeFakeVoterRegistrationPostAction()
    {
        $action = factory(Action::class)
            ->states('voter-registration')
            ->create();

        config(['import.rock_the_vote.post.action_id' => $action->id]);

        return $action;
    }

    /**
     * Return a mock "Started registration" field value.
     *
     * @param Int $days
     * @return string
     */
    public function daysAgoInRockTheVoteFormat($days = 0)
    {
        return Carbon::now()
            ->subDays($days)
            ->format('Y-m-d H:i:s O');
    }

    /**
     * Test that user and post are created if user is not found.
     */
    public function testCreatesUserIfUserNotFound()
    {
        $payload = $this->makeFakeReportPayload();

        $this->makeFakeVoterRegistrationPostAction();

        $importFile = factory(ImportFile::class)
            ->states('rock_the_vote')
            ->create();

        ImportRockTheVoteRecord::dispatch($payload, $importFile);

        $this->assertMongoDatabaseHas('users', [
            'first_name' => $payload['First name'],
        ]);

        $user = User::where('first_name', $payload['First name'])->first();

        $this->assertMysqlDatabaseHas('posts', [
            'northstar_id' => $user->id,
            'type' => 'voter-reg',
        ]);

        $this->assertMysqlDatabaseHas('rock_the_vote_logs', [
            'import_file_id' => $importFile->id,
            'status' => $payload['Status'],
            'user_id' => $user->id,
        ]);
    }

    /**
     *  Test that user is not created if user is found with provided user import data.
     *
     * @return void
     */
    public function testDoesNotCreateUserIfUserFound()
    {
        $user = factory(User::class)->create();

        $payload = $this->makeFakeReportPayloadForSpecificUser($user, [
            'Status' => 'Step 1',
        ]);

        $this->makeFakeVoterRegistrationPostAction();

        $importFile = factory(ImportFile::class)
            ->states('rock_the_vote')
            ->create();

        ImportRockTheVoteRecord::dispatch($payload, $importFile);

        $this->assertMysqlDatabaseHas('posts', [
            'northstar_id' => $user->id,
            'type' => 'voter-reg',
        ]);

        $this->assertMysqlDatabaseHas('rock_the_vote_logs', [
            'import_file_id' => $importFile->id,
            'status' => $payload['Status'],
            'user_id' => $user->id,
        ]);
    }

    /**
     * Test that a password reset email is not sent when a new user is created
     * with a Step 1 status.
     */
    public function testDoesNotSendPasswordResetForNewUserWithStep1Status()
    {
        $payload = $this->makeFakeReportPayload([
            'First name' => 'Puppet',
            'Last name' => 'Sloth',
            'Email address' => 'puppetsloth@dosomething.org',
            'Status' => 'Step 1',
        ]);

        $this->makeFakeVoterRegistrationPostAction();

        $importFile = factory(ImportFile::class)
            ->states('rock_the_vote')
            ->create();

        ImportRockTheVoteRecord::dispatch($payload, $importFile);

        $user = User::where('email', 'puppetsloth@dosomething.org')->first();

        $this->assertNoCustomerIoEvent($user, 'rock-the-vote-activate-account');
    }

    /**
     * Test that record is not imported if it has already been logged as an imported record.
     */
    public function testDoesNotImportRecordIfLogExists()
    {
        $user = factory(User::class)->create();

        $payload = $this->makeFakeReportPayloadForSpecificUser($user);

        $this->makeFakeVoterRegistrationPostAction();

        factory(RockTheVoteLog::class)->create([
            'started_registration' => $payload['Started registration'],
            'status' => $payload['Status'],
            'user_id' => $user->id,
        ]);

        $importFile = factory(ImportFile::class)
            ->states('rock_the_vote')
            ->create();

        ImportRockTheVoteRecord::dispatch($payload, $importFile);

        $this->assertMysqlDatabaseHas('import_files', [
            'id' => $importFile->id,
            'skip_count' => 1,
        ]);
    }

    /**
     * Test that record is skipped if the RTV data is invalid.
     *
     * @return void
     */
    public function testSkipsImportIfInvalidRecordData()
    {
        $payload = $this->makeFakeReportPayload([
            'First name' => 'Puppet',
            'Last name' => 'Sloth',
            RockTheVoteRecord::$startedRegistrationFieldName => '555-555-5555',
        ]);

        $importFile = factory(ImportFile::class)
            ->states('rock_the_vote')
            ->create();

        ImportRockTheVoteRecord::dispatch($payload, $importFile);

        $this->assertMysqlDatabaseHas('import_files', [
            'id' => $importFile->id,
            'skip_count' => 1,
        ]);
    }
}
