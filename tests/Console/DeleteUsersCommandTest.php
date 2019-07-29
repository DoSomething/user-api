<?php

use Northstar\Models\User;

class DeleteUsersCommandTest extends TestCase
{
    /** @test */
    public function it_should_delete_users()
    {
        $input = 'tests/Console/example-identify-output.csv';

        // Create the expected users we're going to destroy:
        $user1 = factory(User::class)->create(['_id' => '5d3630a0fdce2742ff6c64d4'])->first();
        $user2 = factory(User::class)->create(['_id' => '5d3630a0fdce2742ff6c64d5'])->first();

        // Run the 'northstar:id' command on the 'example-identify-input.csv' file:
        $this->artisan('northstar:delete', ['input' => $input]);

        // The command should remove
        $this->assertAnonymized($user1);
        $this->assertAnonymized($user2);
    }

    /**
     * Assert that the given model has been anonymized.
     *
     * @param User $user
     */
    protected function assertAnonymized($user)
    {
        $attributes = $user->fresh()->getAttributes();

        // We should not see any fields with PII:
        $this->assertArrayNotHasKey('email', $attributes);
        $this->assertArrayNotHasKey('first_name', $attributes);
        $this->assertArrayNotHasKey('last_name', $attributes);
        $this->assertArrayNotHasKey('addr_street1', $attributes);
        $this->assertArrayNotHasKey('addr_street2', $attributes);

        // ...but we should still have some demographic fields:
        $this->assertArrayHasKey('addr_zip', $attributes);
    }
}
