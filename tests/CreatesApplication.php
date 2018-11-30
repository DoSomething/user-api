<?php

namespace Tests;

use FakerPhoneNumber;
use DoSomething\Gateway\Blink;
use Illuminate\Contracts\Console\Kernel;

trait CreatesApplication
{
    /**
     * The Blink API client mock.
     *
     * @var \Mockery\MockInterface
     */
    protected $blinkMock;

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
        $app = require __DIR__.'/../bootstrap/app.php';

        $app->make(Kernel::class)->bootstrap();

        // Configure a mock for Blink model events.
        $this->blinkMock = $this->mock(Blink::class);
        $this->blinkMock->shouldReceive('userCreate')->andReturn(true);

        return $app;
    }

    /**
     * Setup the test environment. This is run before *every* single
     * test method, so avoid doing anything that takes too much time!
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        $this->serverVariables = $this->transformHeadersToServerVars($this->headers);

        // Get a new Faker generator from Laravel.
        $this->faker = app(\Faker\Generator::class);
        $this->faker->addProvider(new FakerPhoneNumber($this->faker));

        // Reset the testing database & run migrations.
        app()->make('db')->getMongoDB()->drop();
        $this->artisan('migrate');
    }
}
