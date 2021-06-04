<?php

use App\Imports\RockTheVoteRecord;
use App\Jobs\Imports\ImportRockTheVoteRecord;
use App\Models\Action;
use App\Models\ImportFile;
use App\Models\Post;
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
     * Make a fake unprocessed import file with no completed or skipped imports.
     *
     * @return \App\Models\ImportFile
     */
    public function makeFakeUnprocessedImportFile()
    {
        return factory(ImportFile::class)
            ->states('rock_the_vote')
            ->create([
                'import_count' => 0,
                'skip_count' => 0,
            ]);
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
     * @param int $days
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
     *
     * @return void
     */
    public function testCreatesUserIfUserNotFound()
    {
        $payload = $this->makeFakeReportPayload();

        $this->makeFakeVoterRegistrationPostAction();

        $importFile = $this->makeFakeUnprocessedImportFile();

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
     * That that if an existing completed post is found for the specified user,
     * the import does not create or update the post.
     *
     * @return void
     */
    public function testDoesNotCreateOrUpdatePostIfExistingCompletedPostFound()
    {
        $user = factory(User::class)->create();

        $payload = $this->makeFakeReportPayloadForSpecificUser($user);

        $action = $this->makeFakeVoterRegistrationPostAction();

        $post = factory(Post::class)
            ->state('voter-reg')
            ->create([
                'action_id' => $action->id,
                'northstar_id' => $user->id,
                'status' => 'register-form',
                'details' => json_encode([
                    'Home zip code' => $payload['Home zip code'],
                    'Finish with State' => $payload['Finish with State'],
                    'Pre-Registered' => $payload['Pre-Registered'],
                    'Started registration' => $payload['Started registration'],
                    'Status' => $payload['Status'],
                    'Tracking Source' => $payload['Tracking Source'],
                ]),
            ]);

        $importFile = $this->makeFakeUnprocessedImportFile();

        ImportRockTheVoteRecord::dispatch($payload, $importFile);

        $this->assertMysqlDatabaseHas('rock_the_vote_logs', [
            'import_file_id' => $importFile->id,
            'started_registration' => $payload['Started registration'],
            'status' => $payload['Status'],
            'user_id' => $user->id,
        ]);

        $this->assertMysqlDatabaseHas('posts', [
            'id' => $post->id,
            'status' => $post->status,
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

        $importFile = $this->makeFakeUnprocessedImportFile();

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
     *
     * @return void
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

        $importFile = $this->makeFakeUnprocessedImportFile();

        ImportRockTheVoteRecord::dispatch($payload, $importFile);

        $user = User::where('email', 'puppetsloth@dosomething.org')->first();

        $this->assertNoCustomerIoEvent($user, 'rock-the-vote-activate-account');
    }

    /**
     * Test that record is not imported if it has already been logged as an imported record.
     *
     * @return void
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

        $importFile = $this->makeFakeUnprocessedImportFile();

        ImportRockTheVoteRecord::dispatch($payload, $importFile);

        $this->assertMysqlDatabaseHas('import_files', [
            'id' => $importFile->id,
            'skip_count' => 1,
        ]);

        $this->assertDatabaseMissing(
            'users',
            ['user_id' => $user->id],
            'mongodb',
        );
    }

    /**
     * Test that existing user is not updated with import data if their voter registration status
     * is already completed (at a higher status hierarchy).
     *
     * @return void
     */
    public function testDoesNotUpdateExistingUserIfAlreadyAtHigherStatus()
    {
        $user = factory(User::class)->create([
            'voter_registration_status' => 'registration_complete',
        ]);

        $action = $this->makeFakeVoterRegistrationPostAction();

        $dateTwoDaysAgo = $this->daysAgoInRockTheVoteFormat(2);

        $dateToday = $this->daysAgoInRockTheVoteFormat(0);

        $postFromTwoDaysAgo = factory(Post::class)
            ->state('voter-reg')
            ->create([
                'action_id' => $action->id,
                'northstar_id' => $user->id,
                'status' => 'register-OVR',
                'details' => json_encode([
                    'Home zip code' => $user->addr_zip,
                    'Finish with State' => 'Yes',
                    'Pre-Registered' => 'No',
                    'Started registration' => $dateTwoDaysAgo,
                    'Status' => 'Complete', // @Question: is this correct?
                    'Tracking Source' => 'ads',
                ]),
            ]);

        $payload = $this->makeFakeReportPayloadForSpecificUser($user, [
            'Finish with State' => 'No',
            'Started registration' => $dateToday,
            'Status' => 'Step 1',
        ]);

        $importFile = $this->makeFakeUnprocessedImportFile();

        ImportRockTheVoteRecord::dispatch($payload, $importFile);

        $this->assertMongoDatabaseHas('users', [
            'voter_registration_status' => $user->voter_registration_status,
        ]);

        $this->assertMysqlDatabaseHas('posts', [
            'id' => $postFromTwoDaysAgo->id,
            'status' => $postFromTwoDaysAgo->status,
            'type' => 'voter-reg',
        ]);

        $this->assertMysqlDatabaseHas('rock_the_vote_logs', [
            'import_file_id' => $importFile->id,
            'started_registration' => $payload['Started registration'],
            'status' => $payload['Status'],
            'user_id' => $user->id,
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

        $importFile = $this->makeFakeUnprocessedImportFile();

        ImportRockTheVoteRecord::dispatch($payload, $importFile);

        $this->assertMysqlDatabaseHas('import_files', [
            'id' => $importFile->id,
            'skip_count' => 1,
        ]);
    }

    /**
     * Test that existing user who started registration process is updated with import data if
     * import record has higher voter registration status.
     *
     * @return void
     */
    public function testUpdatesExistingUserIfImportDataHasHigherStatus()
    {
        $user = factory(User::class)->create([
            'voter_registration_status' => 'step-1',
        ]);

        $action = $this->makeFakeVoterRegistrationPostAction();

        $dateRegistrationStarted = $this->daysAgoInRockTheVoteFormat();

        $post = factory(Post::class)
            ->state('voter-reg')
            ->create([
                'action_id' => $action->id,
                'northstar_id' => $user->id,
                'status' => 'step-1',
                'details' => json_encode([
                    'Home zip code' => $user->addr_zip,
                    'Finish with State' => 'No',
                    'Pre-Registered' => 'No',
                    'Started registration' => $dateRegistrationStarted,
                    'Status' => 'Step 1',
                    'Tracking Source' => 'ads',
                ]),
            ]);

        $payload = $this->makeFakeReportPayloadForSpecificUser($user, [
            'Finish with State' => 'No',
            'Started registration' => $dateRegistrationStarted,
            'Status' => 'Step 2',
        ]);

        $importFile = $this->makeFakeUnprocessedImportFile();

        ImportRockTheVoteRecord::dispatch($payload, $importFile);

        $this->assertMongoDatabaseHas('users', [
            'voter_registration_status' => 'step-2',
        ]);

        $this->assertMysqlDatabaseHas('posts', [
            'id' => $post->id,
            'status' => 'step-2',
            'type' => 'voter-reg',
        ]);
    }

    /**
     * Test that a user is updated if their voter registration status changes.
     *
     * @return void
     */
    public function testUpdatesVoterRegistrationStatusIfStatusChanged()
    {
        $user = factory(User::class)->create();

        $action = $this->makeFakeVoterRegistrationPostAction();

        $registrationDate = $this->daysAgoInRockTheVoteFormat();

        $post = factory(Post::class)
            ->state('voter-reg')
            ->create([
                'action_id' => $action->id,
                'northstar_id' => $user->id,
                'status' => 'step-1',
                'details' => json_encode([
                    'Home zip code' => $user->addr_zip,
                    'Finish with State' => 'Yes',
                    'Pre-Registered' => 'No',
                    'Started registration' => $registrationDate,
                    'Status' => 'Step 1',
                    'Tracking Source' => 'ads',
                ]),
            ]);

        $payload = $this->makeFakeReportPayloadForSpecificUser($user, [
            'Finish with State' => 'Yes',
            'Started registration' => $registrationDate,
            'Status' => 'Complete',
        ]);

        $importFile = $this->makeFakeUnprocessedImportFile();

        ImportRockTheVoteRecord::dispatch($payload, $importFile);

        $this->assertMysqlDatabaseHas('rock_the_vote_logs', [
            'import_file_id' => $importFile->id,
            'started_registration' => $payload['Started registration'],
            'status' => $payload['Status'],
            'user_id' => $user->id,
        ]);

        $this->assertMysqlDatabaseHas('posts', [
            'id' => $post->id,
            'status' => 'register-OVR', // status if finishing with state form
        ]);
    }

    /**
     * Test that import mobile number provided is added to user record if previously missing,
     * and no other user exists with the same mobile number.
     *
     * @return void
     */
    public function testUserMobileAddedIfImportMobileProvided()
    {
        $user = factory(User::class)->create(['mobile' => null]);

        $payload = $this->makeFakeReportPayloadForSpecificUser($user, [
            'Phone' => $this->faker->unique()->phoneNumber,
        ]);

        $this->makeFakeVoterRegistrationPostAction();

        $importFile = $this->makeFakeUnprocessedImportFile();

        ImportRockTheVoteRecord::dispatch($payload, $importFile);

        $this->assertMongoDatabaseHas('users', [
            '_id' => $user->id,
            'mobile' => normalize('mobile', $payload['Phone']),
        ]);
    }

    /**
     * Test that user SMS subscription is not updated if import data does not include
     * a mobile phone number.
     */
    public function testSmsSubscriptionIsNotUpdatedIfImportDataMobileNumberIsNull()
    {
        $user = factory(User::class)->create();

        $action = $this->makeFakeVoterRegistrationPostAction();

        $dateRegistrationStarted = $this->daysAgoInRockTheVoteFormat();

        // Make a fake voter registration post that already exists.
        factory(Post::class)
            ->state('voter-reg')
            ->create([
                'action_id' => $action->id,
                'northstar_id' => $user->id,
                'status' => 'step-2',
                'details' => json_encode([
                    'Home zip code' => $user->addr_zip,
                    'Finish with State' => 'Yes',
                    'Pre-Registered' => 'No',
                    'Started registration' => $dateRegistrationStarted,
                    'Status' => 'Step 2',
                    'Tracking Source' => 'ads',
                ]),
            ]);

        $payload = $this->makeFakeReportPayloadForSpecificUser($user, [
            'Phone' => null,
            'Started registration' => $dateRegistrationStarted,
        ]);

        $importFile = $this->makeFakeUnprocessedImportFile();

        ImportRockTheVoteRecord::dispatch($payload, $importFile);

        $this->assertMongoDatabaseHas('users', [
            '_id' => $user->id,
            'mobile' => $user->mobile,
            'sms_status' => $user->sms_status,
            'sms_subscription_topics' => $user->sms_subscription_topics,
        ]);
    }

    /**
     * Test that existing user's SMS subscription is not updated if an import record has already
     * been added with same registration phone number.
     */
    public function testSmsSubscriptionIsNotUpdatedIfLogExistsAndImportPhoneNumberProvided()
    {
        $user = factory(User::class)->create([
            'sms_subscription_topics' => ['general'],
        ]);

        $this->makeFakeVoterRegistrationPostAction();

        $dateRegistrationStarted = $this->daysAgoInRockTheVoteFormat();

        factory(RockTheVoteLog::class)->create([
            'contains_phone' => true,
            'started_registration' => $dateRegistrationStarted,
            'user_id' => $user->id,
        ]);

        $payload = $this->makeFakeReportPayloadForSpecificUser($user, [
            'Phone' => $user->mobile,
            'Started registration' => $dateRegistrationStarted,
        ]);

        $importFile = $this->makeFakeUnprocessedImportFile();

        ImportRockTheVoteRecord::dispatch($payload, $importFile);

        $this->assertMongoDatabaseHas('users', [
            '_id' => $user->id,
            'sms_subscription_topics' => $user->sms_subscription_topics,
        ]);
    }

    /**
     * Test that existing user's mobile number is added if they did not have a number set, and
     * the import data has a number provided.
     */
    public function testMobileNumberIsAddedForExistingUserIfMissingAndImportPhoneNumberProvided()
    {
        $user = factory(User::class)->create([
            'mobile' => null,
        ]);

        $this->makeFakeVoterRegistrationPostAction();

        $phoneNumber = '+15558675309';

        $payload = $this->makeFakeReportPayloadForSpecificUser($user, [
            'Phone' => $phoneNumber,
        ]);

        $importFile = $this->makeFakeUnprocessedImportFile();

        ImportRockTheVoteRecord::dispatch($payload, $importFile);

        $this->assertMongoDatabaseHas('users', [
            '_id' => $user->id,
            'mobile' => $phoneNumber,
        ]);
    }

    /**
     * Test that the owner of the mobile number has their sms subscription topics updated
     * if the import data user does not have a mobile set and the provided number in import
     * data points to a different user owning that nubmer.
     */
    public function testMobileOwnerIsUpdatedIfImportUserHasNoMobileAndMobileIsTaken()
    {
        $webUser = factory(User::class)->create([
            'mobile' => null,
            'sms_subscription_topics' => ['general', 'voting'],
        ]);

        $phoneNumber = '+15558675309';

        $mobileUser = factory(User::class)->create([
            'mobile' => $phoneNumber,
            'sms_subscription_topics' => ['general', 'voting'],
        ]);

        $this->makeFakeVoterRegistrationPostAction();

        $payload = $this->makeFakeReportPayloadForSpecificUser($webUser, [
            'Phone' => $phoneNumber,
        ]);

        $importFile = $this->makeFakeUnprocessedImportFile();

        ImportRockTheVoteRecord::dispatch($payload, $importFile);

        $this->assertMongoDatabaseHas('users', [
            '_id' => $webUser->id,
            'sms_subscription_topics' => ['general', 'voting'],
        ]);

        $this->assertMongoDatabaseHas('users', [
            '_id' => $mobileUser->id,
            'sms_subscription_topics' => ['general'],
        ]);

        // @Question: this test in Chompy had something about the mobile user's sms_status
        // being set to "stop", but doesn't seem like it is coded that way in the job.
    }
}
