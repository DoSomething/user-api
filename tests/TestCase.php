<?php

namespace Tests;

use App\Jobs\Job;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Mockery;
use Tests\CreatesApplication;
use Tests\WithAuthentication;
use Tests\WithMocks;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication, RefreshDatabase, WithMocks, WithAuthentication;

    /**
     * The base URL to use while testing the application.
     *
     * @var string
     */
    protected $baseUrl = 'http://localhost';

    /**
     * Default headers for this test case.
     *
     * @var array
     */
    protected $headers = [];

    /**
     * Get the raw Mongo document for inspection.
     *
     * @param $collection - Mongo Collection name
     * @param array $contents
     * @return bool
     */
    public function createMongoDocument($collection, array $contents)
    {
        $document = app('db')
            ->connection('mongodb')
            ->collection($collection)
            ->insert($contents);

        return $document;
    }

    /**
     * Get the raw Mongo document for inspection.
     *
     * @param $collection - Mongo Collection name
     * @param $id - The _id of the document to fetch
     * @return array
     */
    public function getMongoDocument($collection, $id)
    {
        $document = app('db')
            ->connection('mongodb')
            ->collection($collection)
            ->where(['_id' => $id])
            ->first();

        $this->assertNotNull(
            $document,
            sprintf(
                'Unable to find document in collection [%s] with _id [%s].',
                $collection,
                $id,
            ),
        );

        return $document;
    }

    /**
     * Assert that a given where condition exists in the MySQL database.
     *
     * @param  string  $table
     * @param  array  $data
     * @return $this
     */
    protected function assertMysqlDatabaseHas($table, array $data)
    {
        return $this->assertDatabaseHas($table, $data, 'mysql');
    }

    /**
     * Assert that a given where condition does not exist in the MySQL database.
     *
     * @param  string  $table
     * @param  array  $data
     * @return $this
     */
    protected function assertMysqlDatabaseMissing($table, array $data)
    {
        return $this->assertDatabaseMissing($table, $data, 'mysql');
    }

    /**
     * Assert that a given where condition exists in the MongoDB database.
     *
     * @param  string  $table
     * @param  array  $data
     * @return $this
     */
    protected function assertMongoDatabaseHas($table, array $data)
    {
        return $this->assertDatabaseHas($table, $data, 'mongodb');
    }

    /**
     * Assert that the given Customer.io event was fired.
     *
     * @param User $user
     * @param string $name
     */
    public function assertCustomerIoEvent(User $user, string $eventName)
    {
        $userMatcher = Mockery::on(function ($argument) use ($user) {
            return $user->is($argument);
        });

        // We should have tracked this event for the provided user:
        return $this->customerIoMock
            ->shouldHaveReceived('trackEvent')
            ->with($userMatcher, $eventName, Mockery::any());
    }

    /**
     * Assert that the given Customer.io event was not fired.
     *
     * @param User $user
     * @param string $name
     */
    public function assertNoCustomerIoEvent(User $user, string $eventName)
    {
        $userMatcher = Mockery::on(function ($argument) use ($user) {
            return $user->is($argument);
        });

        // We should not have tracked this event for the provided user:
        return $this->customerIoMock->shouldNotHaveReceived('trackEvent', [
            $userMatcher,
            $eventName,
            Mockery::any(),
        ]);
    }

    /**
     * Dispatch the given job, even when Bus is mocked.
     *
     * @param mixed $job
     * @return $this
     */
    protected function forceDispatch(Job $job)
    {
        // Directly call the 'handle' method on the given job, bypassing potentially
        // mocked Dispatcher, and inject any needed dependencies from the container:
        $this->app->call([$job, 'handle']);

        return $this;
    }

    /**
     * Submit a form on the page without crawling the returned page. Useful for
     * when a form results in an external redirect that'd break test crawler.
     *
     * @param  string  $buttonText
     * @param  array  $inputs
     * @return $this
     */
    public function postForm($buttonText, array $inputs = [])
    {
        $form = $this->fillForm($buttonText, $inputs);

        $this->call(
            $form->getMethod(),
            $form->getUri(),
            $this->extractParametersFromForm($form),
        );

        return $this;
    }

    /**
     * Register a new user account.
     */
    public function registerUpdated()
    {
        // Make sure we're logged out before trying to register.
        auth('web')->logout();

        return $this->post('/register', [
            'first_name' => $this->faker->firstName,
            'last_name' => $this->faker->lastName,
            'email' => $this->faker->unique->email,
            'password' => $this->faker->password(10),
        ]);
    }

    /**
     * Assert the given models are soft-deleted and the provided
     * fields have been nulled out.
     *
     * @param Collection $collection
     * @param array $fields
     */
    public function assertAnonymized(Collection $collection, array $fields = [])
    {
        foreach ($collection as $model) {
            $identifier = [$model->getKeyName() => $model->getKey()];
            $nulledFields = array_fill_keys($fields, null);

            $this->assertSoftDeleted(
                $model->getTable(),
                array_merge($identifier, $nulledFields),
                $model->getConnectionName(),
                $model->getDeletedAtColumn(),
            );
        }
    }

    /**
     * Assert that the given model has been anonymized.
     *
     * @param User $before
     */
    protected function assertUserAnonymized(User $before)
    {
        $after = $before->fresh();
        $attributes = $after->getAttributes();

        // The birthdate should be set to January 1st of the same year:
        $this->assertEquals($before->birthdate->year, $after->birthdate->year);
        $this->assertEquals(1, $after->birthdate->month);
        $this->assertEquals(1, $after->birthdate->day);

        // We should not see any fields with PII:
        $this->assertArrayNotHasKey('email', $attributes);
        $this->assertArrayNotHasKey('first_name', $attributes);
        $this->assertArrayNotHasKey('last_name', $attributes);
        $this->assertArrayNotHasKey('addr_street1', $attributes);
        $this->assertArrayNotHasKey('addr_street2', $attributes);

        // ...but we should still have some demographic fields:
        $this->assertArrayHasKey('addr_zip', $attributes);

        // We should also have set a "deleted at" flag:
        $this->assertArrayHasKey('deleted_at', $attributes);
    }
}
