<?php

namespace Tests\Http\Web;

use App\Models\Group;
use App\Models\GroupType;
use App\Models\User;
use Tests\TestCase;

class WebGroupTest extends TestCase
{
    /** @test */
    public function testAdminCanCreateGroup()
    {
        $admin = factory(User::class)->states('admin')->create();

        $groupType = factory(GroupType::class)->create();

        $name = $this->faker->sentence;

        $response = $this->actingAs($admin, 'web')->post('/admin/groups', [
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
        $admin = factory(User::class)->states('admin')->create();

        $group = factory(Group::class)->create();

        $response = $this->actingAs($admin, 'web')->post('/admin/groups', [
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
        $staff = factory(User::class)->states('staff')->create();

        $groupType = factory(GroupType::class)->create();

        $name = $this->faker->sentence;

        $response = $this->actingAs($staff, 'web')->post('/admin/groups', [
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
