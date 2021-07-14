<?php

namespace Tests\Models;

use App\Models\User;
use Tests\TestCase;

class ModelTest extends TestCase
{
    /** @test */
    public function it_should_unset_null_fields()
    {
        /** @var User $user */
        $user = factory(User::class)->create([
            'mobile' => $this->faker->phoneNumber,
            'last_name' => null,
        ]);

        // Now, unset some fields.
        $user->mobile = null;
        $user->last_name = null;
        $user->save();

        // Make sure the field is unset on the actual document.
        $document = $this->getMongoDocument('users', $user->id);

        $this->assertArrayNotHasKey('mobile', $document);
        $this->assertArrayNotHasKey('last_name', $document);
    }

    /** @test */
    public function it_should_set_audits_field_for_audited_class()
    {
        /** @var User $user */
        $user = factory(User::class)->create();

        // Freeze time to be able to test it.
        $time = $this->mockTime();

        // Set an attribute.
        $user->first_name = 'Jill';
        $user->save();

        // Make sure the audit prop with audit info is added for the set attribute.
        $document = $this->getMongoDocument('users', $user->id);

        $this->assertArrayHasKey('audit', $document);
        $this->assertEquals(
            ['source' => 'northstar', 'updated_at' => $time],
            $user->audit['first_name'],
        );
    }
}
