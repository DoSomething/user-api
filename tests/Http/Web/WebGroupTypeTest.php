<?php

namespace Tests\Http\Web;

use App\Models\GroupType;
use App\Models\User;
use Tests\TestCase;

class WebGroupTypeTest extends TestCase
{
    /** @test */
    public function testAdminCanCreateGroupType()
    {
        $admin = factory(User::class)->states('admin')->create();

        $name = $this->faker->sentence;

        $responseOne = $this->actingAs($admin, 'web')->post(
            '/admin/group-types',
            [
                'name' => $name,
                'filter_by_location' => true,
            ],
        );

        $responseOne->assertRedirect();

        $this->assertMysqlDatabaseHas('group_types', [
            'name' => $name,
            'filter_by_location' => 1,
        ]);
    }

    /** @test */
    public function testAdminCannotCreateDuplicateGroupType()
    {
        $admin = factory(User::class)->states('admin')->create();

        $groupType = factory(GroupType::class)->create();

        $response = $this->actingAs($admin, 'web')->post('/admin/group-types', [
            'name' => $groupType->name,
        ]);

        $response->assertSessionHasErrors();
    }

    /** @test */
    public function testStaffCannotCreateGroupType()
    {
        $staff = factory(User::class)->states('staff')->create();

        $name = $this->faker->sentence;

        $response = $this->actingAs($staff, 'web')->post('/admin/group-types', [
            'name' => $name,
        ]);

        $response->assertRedirect();

        $this->assertDatabaseMissing(
            'group_types',
            [
                'name' => $name,
            ],
            'mysql',
        );
    }

    /** @test */
    public function testUnsettingFilterByState()
    {
        $admin = factory(User::class)->states('admin')->create();

        $groupType = factory(GroupType::class)->create([
            'filter_by_location' => true,
        ]);

        $response = $this->actingAs(
            $admin,
            'web',
        )->put("/admin/group-types/$groupType->id", ['name' => 'Test 123']);

        $response->assertRedirect();

        $this->assertMysqlDatabaseHas('group_types', [
            'id' => $groupType->id,
            'filter_by_location' => 0,
        ]);
    }
}
