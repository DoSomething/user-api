<?php

use Carbon\Carbon;
use Socialite;
use DoSomething\Gateway\Blink;
use League\OAuth2\Server\CryptKey;
use Northstar\Auth\Entities\AccessTokenEntity;
use Northstar\Auth\Entities\ClientEntity;
use Northstar\Auth\Entities\ScopeEntity;
use Northstar\Auth\Scope;
use Northstar\Models\Client;
use Northstar\Models\Token;
use Northstar\Models\User;
use Northstar\Services\Phoenix;

abstract class TestCase extends Illuminate\Foundation\Testing\TestCase
{
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
     * The Faker generator, for creating test data.
     *
     * @var \Faker\Generator
     */
    protected $faker;

    /**
     * The Phoenix API client mock.
     *
     * @var \Mockery\MockInterface
     */
    protected $phoenixMock;

    /**
     * The Blink API client mock.
     *
     * @var \Mockery\MockInterface
     */
    protected $blinkMock;

    /**
     * Make a new authenticated web user.
     *
     * @return \Northstar\Models\User
     */
    protected function makeAuthWebUser()
    {
        $user = factory(User::class)->create();

        $this->be($user, 'web');

        return $user;
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

        // Configure a mock for Phoenix & default `createDrupalUser` response.
        $this->phoenixMock = $this->mock(Phoenix::class);
        $this->phoenixMock->shouldReceive('createDrupalUser')->andReturnUsing(function () {
            return $this->faker->unique()->numberBetween(1, 30000000);
        });

        // Configure a mock for Blink model events.
        $this->blinkMock = $this->mock(Blink::class);
        $this->blinkMock->shouldReceive('userCreate')->andReturn(true);

        // Reset the testing database & run migrations.
        $this->app->make('db')->getMongoDB()->drop();
        $this->artisan('migrate');
    }

    /**
     * Use the given API key for this request.
     *
     * @param Client $client
     * @return $this
     */
    public function withLegacyApiKey(Client $client)
    {
        $this->serverVariables = array_replace($this->serverVariables, [
            'HTTP_X-DS-REST-API-Key' => $client->client_secret,
        ]);

        return $this;
    }

    /**
     * Set an API key with the given scopes on the request.
     *
     * @param array $scopes
     * @return $this
     */
    public function withLegacyApiKeyScopes(array $scopes)
    {
        $client = Client::create([
            'client_id' => 'testing'.$this->faker->uuid,
            'scope' => $scopes,
        ]);

        $this->withLegacyApiKey($client);

        return $this;
    }

    /**
     * Make the following request as a normal user with the `user` scope.
     *
     * @return $this
     */
    public function asNormalUser()
    {
        $user = factory(User::class)->create();

        return $this->asUser($user, ['user']);
    }

    /**
     * Make the following request as a staff user with the `user` and `role:staff` scopes.
     *
     * @return $this
     */
    public function asStaffUser()
    {
        $staff = factory(User::class, 'staff')->create();

        return $this->asUser($staff, ['user', 'role:staff']);
    }

    /**
     * Make the following request as an admin user with the `user` and `role:admin` scopes.
     *
     * @return $this
     */
    public function asAdminUser()
    {
        $admin = factory(User::class, 'admin')->create();

        return $this->asUser($admin, ['user', 'role:admin']);
    }

    /**
     * Create a signed JWT to authorize resource requests.
     *
     * @param User $user
     * @param array $scopes
     * @return $this
     */
    public function withAccessToken($scopes = [], $user = null)
    {
        $accessToken = new AccessTokenEntity();
        $accessToken->setClient(new ClientEntity('phpunit', 'PHPUnit', $scopes));
        $accessToken->setIdentifier(bin2hex(random_bytes(40)));
        $accessToken->setExpiryDateTime((new \DateTime())->add(new DateInterval('PT1H')));

        if ($user) {
            $accessToken->setUserIdentifier($user->id);
            $accessToken->setRole($user->role);
        }

        foreach ($scopes as $identifier) {
            if (! array_key_exists($identifier, Scope::all())) {
                continue;
            }

            $entity = new ScopeEntity();
            $entity->setIdentifier($identifier);
            $accessToken->addScope($entity);
        }

        $header = 'Bearer '.$accessToken->convertToJWT(new CryptKey(base_path('storage/keys/private.key')));
        $this->serverVariables = array_replace($this->serverVariables, [
            'HTTP_Authorization' => $header,
        ]);

        return $this;
    }

    /**
     * Create a signed JWT to authorize resource requests.
     *
     * @param User $user
     * @param array $scopes
     * @return $this
     */
    public function asUser($user, $scopes = [])
    {
        return $this->withAccessToken($scopes, $user);
    }

    /**
     * Set the currently logged in user for the application. Use this instead of Laravel's
     * built-in $this->actingAs() or $this->be() because it will create an actual token in
     * the database to be manipulated/checked & set proper authentication header.
     *
     * @param User $user
     * @return $this
     */
    public function asUserUsingLegacyAuth(User $user)
    {
        // Create a legacy token.
        $token = Token::create(['user_id' => $user->id]);

        $this->serverVariables = array_replace($this->serverVariables, [
            'HTTP_Authorization' => 'Bearer '.$token->key,
        ]);

        return $this;
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
        $document = $this->app->make('db')->collection($collection)->where(['_id' => $id])->first();

        $this->assertNotNull($document, sprintf(
            'Unable to find document in collection [%s] with _id [%s].', $collection, $id
        ));

        return $document;
    }

    /**
     * Creates the application.
     *
     * @return \Illuminate\Foundation\Application
     */
    public function createApplication()
    {
        $app = require __DIR__.'/../bootstrap/app.php';

        $app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

        return $app;
    }

    /**
     * Mock a class, and register with the IoC container.
     *
     * @param $class String - Class name to mock
     * @return \Mockery\MockInterface
     */
    public function mock($class)
    {
        $mock = Mockery::mock($class);

        $this->app->instance($class, $mock);

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

        $this->app->instance($class, $spy);

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

        $this->call($form->getMethod(), $form->getUri(), $this->extractParametersFromForm($form));

        return $this;
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
     * Register a new user account.
     */
    public function register()
    {
        // Make sure we're logged out before trying to register.
        auth('web')->logout();

        $this->visit('register');
        $this->submitForm('register-submit', [
            'first_name' => $this->faker->firstName,
            'email' => $this->faker->unique->email,
            'birthdate' => $this->faker->date('m/d/Y', '5 years ago'),
            'password' => 'secret',
            'password_confirmation' => 'secret',
        ]);
    }
}
