<?php

use App\Auth\Registrar;
use App\Jobs\Imports\ImportEmailSubscriptions;
use App\Models\ImportFile;
use App\Models\User;
use Illuminate\Support\Facades\App;

class ImportEmailSubscriptionsTest extends TestCase
{
    /**
     * Test that an existing user record can have email subcriptions updated
     * when importing records.
     */
    public function testAddsSubscriptionForExistingUser()
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
    public function testAddsSubcriptionForNewUser()
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

    /**
     * Test that importing an email subscription for existing user with prior subscription
     * topics, appends the new one to the list.
     */
    public function testNoDuplicateEmailSubscriptionTopics()
    {
        $user = factory(User::class)
            ->states('email-subscribed-community')
            ->create();

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
     * Test that importing an email subscription for a new user triggers password reset.
     */
    public function testImportTriggersPasswordResetForNewUser()
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
                'email_subscription_topic' => 'community',
            ],
        );

        $user = App::make(Registrar::class)->resolve([
            'email' => 'puppetsloth@dosomething.org',
        ]);

        $this->assertCustomerIoEvent($user, 'call_to_action_email');
    }
}
