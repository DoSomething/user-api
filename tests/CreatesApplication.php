<?php

namespace Tests;

use Carbon\Carbon;
use FakerPhoneNumber;
use FakerSchoolId;
use Illuminate\Contracts\Console\Kernel;

trait CreatesApplication
{
    /**
     * The Customer.io API client mock.
     *
     * @var \Mockery\MockInterface
     */
    protected $customerIoMock;

    /**
     * The GraphQL client mock.
     *
     * @var \Mockery\MockInterface
     */
    protected $graphqlMock;

    /**
     * The Faker generator, for creating test data.
     *
     * @var \Faker\Generator
     */
    protected $faker;

    /**
     * Creates the application.
     *
     * @return \Illuminate\Foundation\Application
     */
    public function createApplication()
    {
        $app = require __DIR__ . '/../bootstrap/app.php';

        $app->make(Kernel::class)->bootstrap();

        // Configure a mock for any Customer.io API calls.
        $this->customerIoMock = $this->mock(\App\Services\CustomerIo::class);
        $this->customerIoMock->shouldReceive('getAttributes')->andReturn(null);
        $this->customerIoMock->shouldReceive('updateCustomer')->andReturn(null);
        $this->customerIoMock->shouldReceive('trackEvent');
        $this->customerIoMock->shouldReceive('sendEmail');
        $this->customerIoMock->shouldReceive('deleteCustomer');
        $this->customerIoMock->shouldReceive('suppressCustomer');

        $this->fastlyMock = $this->mock(\App\Services\Fastly::class);
        $this->fastlyMock->shouldReceive('purge');

        $this->gambitMock = $this->mock(\App\Services\Gambit::class);
        $this->gambitMock->shouldReceive('deleteUser');

        $this->rockTheVoteMock = $this->mock(\App\Services\RockTheVote::class);

        // Configure a mock for GraphQL calls.
        $this->graphqlMock = $this->mock(\App\Services\GraphQL::class);
        $this->graphqlMock->shouldReceive('getSchoolById')->andReturn([
            'name' => 'San Dimas High School',
            'location' => 'US-CA',
        ]);
        $this->graphqlMock->shouldReceive('getClubById')->andReturn([
            'name' => 'DoSomething Staffers Club',
            'leaderId' => new \MongoDB\BSON\ObjectId(),
        ]);
        $this->graphqlMock
            ->shouldReceive('getCampaignWebsiteByCampaignId')
            ->andReturn([
                'title' => 'Test Example Campaign',
                'slug' => 'test-example-campaign',
            ]);

        return $app;
    }

    /**
     * Setup the test environment. This is run before *every* single
     * test method, so avoid doing anything that takes too much time!
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->serverVariables = $this->transformHeadersToServerVars(
            $this->headers,
        );

        // Get a new Faker generator from Laravel.
        $this->faker = app(\Faker\Generator::class);
        $this->faker->addProvider(new FakerPhoneNumber($this->faker));
        $this->faker->addProvider(new FakerSchoolId($this->faker));

        // Reset to the current time, if mocked.
        Carbon::setTestNow(null);

        // Reset the testing database & run migrations.
        $mongoConnection = app('db')->connection('mongodb');
        $mongoConnection->getMongoDB()->drop();
        $this->artisan('migrate', [
            '--database' => 'mongodb',
            '--path' => 'database/migrations-mongodb',
        ]);
    }
}
