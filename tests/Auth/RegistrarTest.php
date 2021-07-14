<?php

namespace Tests\Auth;

use App\Auth\Registrar;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Str;
use Tests\BrowserKitTestCase;

class RegistrarTest extends BrowserKitTestCase
{
    /**
     * Test that we can resolve by ID.
     */
    public function testResolveById()
    {
        $registrar = $this->app->make(Registrar::class);

        // Fill some other random users.
        factory(User::class, 2)->create();

        // Make a test user to resolve.
        $user = factory(User::class)->create();
        $resolvedUser = $registrar->resolve(['id' => $user->id]);

        $this->assertSame($user->id, $resolvedUser->id);
    }

    /**
     * Test that we can resolve by email.
     */
    public function testResolveByEmail()
    {
        $registrar = $this->app->make(Registrar::class);

        // Fill some other random users.
        factory(User::class, 2)->create();

        // Make a test user to resolve.
        $user = factory(User::class)->create();
        $resolvedUser = $registrar->resolve([
            'email' => Str::upper($user->email),
        ]);

        $this->assertSame($user->id, $resolvedUser->id);
    }

    /**
     * Test that we can resolve by mobile.
     */
    public function testResolveByMobile()
    {
        $registrar = $this->app->make(Registrar::class);

        // Fill some other random users.
        factory(User::class, 2)->create();

        // Make a test user to resolve.
        $user = factory(User::class)->create(['mobile' => '1235551234']);
        $resolvedUser = $registrar->resolve(['mobile' => '1 (123) 555-1234']);

        $this->assertSame($user->id, $resolvedUser->id);
    }

    /**
     * Test that we can resolve by multiple matching fields.
     */
    public function testResolveByMultiple()
    {
        $registrar = $this->app->make(Registrar::class);

        // Fill some other random users.
        factory(User::class, 2)->create();

        // Make a test user to resolve.
        $user = factory(User::class)->create([
            'email' => 'test@dosomething.org',
            'mobile' => '1235551234',
        ]);
        $resolvedUser = $registrar->resolve([
            'email' => 'Test@DoSomething.org',
            'mobile' => '1 (123) 555-1234',
        ]);

        $this->assertSame($user->id, $resolvedUser->id);
    }

    /**
     * Test that we return null if we can't conclusively resolve one account.
     */
    public function testResolveInconclusive()
    {
        $registrar = $this->app->make(Registrar::class);

        // Make two test users.
        factory(User::class)->create(['email' => 'test@dosomething.org']);
        factory(User::class)->create(['mobile' => '1235551234']);

        // Try to resolve one user with each of those credentials...
        $resolvedUser = $registrar->resolve([
            'email' => 'Test@DoSomething.org',
            'mobile' => '1 (123) 555-1234',
        ]);

        $this->assertNull($resolvedUser);
    }

    /**
     * Test that we return null if we try to resolve by non-unique fields.
     */
    public function testResolveByNonUniqueFields()
    {
        $registrar = $this->app->make(Registrar::class);

        // Make a test user to (not) resolve.
        factory(User::class)->create([
            'first_name' => 'Sylvanas',
            'last_name' => 'Windrunner',
        ]);

        $resolvedUser = $registrar->resolve([
            'first_name' => 'Sylvanas',
            'last_name' => 'Windrunner',
        ]);

        $this->assertNull($resolvedUser);
    }

    /**
     * Test that we return null if we try to resolve by non-unique fields.
     */
    public function testDoesNotResolveByPassword()
    {
        $registrar = $this->app->make(Registrar::class);

        // Make a test user to (not) resolve.
        $user = factory(User::class)->create(['password' => 'secret']);

        $resolvedUser = $registrar->resolve([
            'email' => $user->email,
            'password' => 'open-sesame',
        ]);

        // It should still resolve even though a wrong password was provided.
        $this->assertSame($user->id, $resolvedUser->id);
    }

    /**
     * Test that we throw an exception if using resolveOrFail().
     */
    public function testResolveFails()
    {
        $registrar = $this->app->make(Registrar::class);

        // Trying to resolve a user that doesn't exist should throw exception:
        $this->expectException(ModelNotFoundException::class);
        $registrar->resolveOrFail([
            'email' => 'ThisDoesNotExist@DoSomething.org',
        ]);
    }

    /**
     * Test that we can validate a user's credentials.
     */
    public function testValidateCredentials()
    {
        $registrar = $this->app->make(Registrar::class);

        $user = factory(User::class)->create(['password' => 'secret']);

        // It should refuse the wrong credentials...
        $this->assertFalse(
            $registrar->validateCredentials($user, ['password' => 'Secret']),
        );
        $this->assertFalse(
            $registrar->validateCredentials($user, ['password' => 's3cr3t']),
        );

        // ... but it should accept the correct ones. Security!
        $this->assertTrue(
            $registrar->validateCredentials($user, ['password' => 'secret']),
        );
    }

    /**
     * Test that we trigger the correct events when validating credentials.
     */
    public function testSuccessfulAuthenticationEvent()
    {
        $registrar = $this->app->make(Registrar::class);
        $user = factory(User::class)->create(['password' => 'secret']);

        $this->expectsEvents(
            \Illuminate\Auth\Events\Login::class,
        )->doesntExpectEvents(\Illuminate\Auth\Events\Failed::class);

        $registrar->validateCredentials($user, ['password' => 'secret']);
    }

    /**
     * Test that we trigger the correct events when rejecting credentials.
     */
    public function testFailedAuthenticationEvent()
    {
        $registrar = $this->app->make(Registrar::class);
        $user = factory(User::class)->create(['password' => 'secret']);

        $this->doesntExpectEvents(
            \Illuminate\Auth\Events\Login::class,
        )->expectsEvents(\Illuminate\Auth\Events\Failed::class);

        $registrar->validateCredentials($user, [
            'password' => 'not-the-password',
        ]);
    }
}
