<?php

use App\Jobs\Imports\ImportEmailSubscriptions;
use App\Models\ImportFile;
use App\Models\User;

class ImportEmailSubscriptionsTest extends TestCase
{
    /**
     * Test that an existing user record can have email subcriptions updated
     * when importing records.
     */
    public function testImportingEmailSubscriptionForExistingUser()
    {
        $user = factory(User::class)->create();

        $importFile = factory(ImportFile::class)
            ->states('email_subscription')
            ->create();

        ImportEmailSubscriptions::dispatch(
            ['email' => $user->email, 'first_name' => $user->first_name],
            $importFile,
            [
                'source_detail' => 'phpunit',
                'email_subscription_topic' => 'community',
            ],
        );

        $this->assertMysqlDatabaseHas('import_files', [
            'import_count' => 1,
            'import_type' => 'email-subscription',
        ]);

        $this->assertMongoDatabaseHas('users', [
            '_id' => $user->id,
            'email_subscription_topics' => ['community'],
        ]);
    }

    /**
     * Test that importing a record for a new user creates a user record and
     * adds their email subscription.
     */
    public function testImportingEmailSubcriptionForNewUser()
    {
        $importFile = factory(ImportFile::class)
            ->states('email_subscription')
            ->create();

        ImportEmailSubscriptions::dispatch(
            [
                'email' => 'puppetsloth@dosomething.org',
                'first_name' => 'puppet',
            ],
            $importFile,
            [
                'source_detail' => 'phpunit',
                'email_subscription_topic' => 'lifestyle',
            ],
        );

        $this->assertMysqlDatabaseHas('import_files', [
            'import_count' => 1,
            'import_type' => 'email-subscription',
        ]);

        $this->assertMongoDatabaseHas('users', [
            'email' => 'puppetsloth@dosomething.org',
            'email_subscription_topics' => ['lifestyle'],
            'first_name' => 'puppet',
        ]);
    }
}
