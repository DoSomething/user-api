<?php

namespace Tests;

use Mockery;
use Carbon\Carbon;

trait WithMocks
{
    /**
     * Mock a class, and register with the IoC container.
     *
     * @param $class String - Class name to mock
     * @return \Mockery\MockInterface
     */
    public function mock($class)
    {
        $mock = Mockery::mock($class);

        app()->instance($class, $mock);

        return $mock;
    }

    /**
     * Spy on a class.
     *
     * @param $class String - Class name to mock
     * @return \Mockery\MockInterface
     */
    public function spy($class)
    {
        $spy = Mockery::spy($class);

        app()->instance($class, $spy);

        return $spy;
    }

    /**
     * "Freeze" time so we can make assertions based on it.
     *
     * @param string $time
     * @return Carbon
     */
    public function mockTime($time = 'now')
    {
        Carbon::setTestNow((string) new Carbon($time));

        return Carbon::getTestNow();
    }
}
