<?php

use App\Models\Group;
use App\Models\GroupType;
use App\Models\User;

class WebGroupTest extends TestCase
{
    /** @test */
    public function testAdminCanCreateGroup()
    {
        $admin = factory(User::class, 'admin')->make();

        $groupType = factory(GroupType::class)->create();

        $name = $this->faker->sentence;

        $response = $this->actingAs($admin, 'web')->post('/groups', [
            'group_type_id' => $groupType->id,
            'name' => $name,
        ]);

        $response->assertRedirect();

        $this->assertMysqlDatabaseHas('groups', [
            'group_type_id' => $groupType->id,
            'name' => $name,
        ]);
    }

    /** @test */
    public function testAdminCannotCreateDuplicateGroup()
    {
        $admin = factory(User::class, 'admin')->make();

        $group = factory(Group::class)->create();

        $response = $this->actingAs($admin, 'web')->post('/groups', [
            'group_type_id' => $group->group_type_id,
            'name' => $group->name,
        ]);

        // Validation fails due to duplicate resource.
        $response->assertSessionHasErrors([
            'name' => 'The name has already been taken.',
        ]);
    }

    /** @test */
    public function testStaffCanCreateGroup()
    {
        $staff = factory(User::class, 'staff')->make();

        $groupType = factory(GroupType::class)->create();

        $name = $this->faker->sentence;

        $response = $this->actingAs($staff, 'web')->post('/groups', [
            'group_type_id' => $groupType->id,
            'name' => $name,
        ]);

        $response->assertRedirect();

        $this->assertMysqlDatabaseHas('groups', [
            'group_type_id' => $groupType->id,
            'name' => $name,
        ]);
    }
}
