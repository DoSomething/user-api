<?php

namespace Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Arr;
use Laravel\BrowserKitTesting\TestCase as BrowserKitBaseTestCase;
use PHPUnit\Framework\Assert;
use Tests\CreatesApplication;
use Tests\WithAuthentication;
use Tests\WithMocks;

abstract class BrowserKitTestCase extends BrowserKitBaseTestCase
{
    use CreatesApplication, WithMocks, WithAuthentication, RefreshDatabase;

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
     * Assert that a given where condition exists in the MongoDB database.
     *
     * @param  string  $table
     * @param  array  $data
     * @return $this
     */
    protected function seeInMongoDatabase($table, array $data)
    {
        return $this->seeInDatabase($table, $data, 'mongodb');
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
     * Register a new user account with updated registration page.
     */
    public function registerUpdated()
    {
        // Make sure we're logged out before trying to register.
        auth('web')->logout();

        $this->visit('register');
        $this->submitForm('register-submit', [
            'first_name' => $this->faker->firstName,
            'last_name' => $this->faker->lastName,
            'email' => $this->faker->unique->email,
            'password' => 'my-top-secret-passphrase',
        ]);
    }

    /**
     * Set a header on the request.
     *
     * @param $name
     * @param $value
     * @return $this
     */
    public function withHeader($name, $value)
    {
        $header = $this->transformHeadersToServerVars([$name => $value]);
        $this->serverVariables = array_merge($this->serverVariables, $header);

        return $this;
    }

    /**
     * Assert that the JSON response does not have the given field.
     *
     * @param  string|array  $key - The JSON path, in "dot notation".
     * @return $this
     */
    public function dontSeeJsonField($key)
    {
        $responseData = $this->response->decodeResponseJson();

        if (Arr::has($responseData, $key)) {
            Assert::fail('Did not expect to find JSON response at ' . $key);
        }

        return $this;
    }

    /**
     * Assert that the JSON response has the given field.
     *
     * @param  string|array  $key - The JSON path, in "dot notation".
     * @param  mixed $expected - Optionally, the expected value to assert.
     * @return $this
     */
    public function seeJsonField($key, $expected = null)
    {
        $responseData = $this->response->decodeResponseJson();

        if (!Arr::has($responseData, $key)) {
            Assert::fail('Expected to find JSON response at ' . $key);
        }

        $actual = Arr::get($responseData, $key);
        if ($expected !== null && $actual !== $expected) {
            Assert::fail(
                'Expected to find "' .
                    $expected .
                    '" in response at ' .
                    $key .
                    ', found: ' .
                    $actual,
            );
        }

        return $this;
    }
}
