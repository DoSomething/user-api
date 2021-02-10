<?php

use App\Models\GroupType;

class GroupTypeTest extends TestCase
{
    /**
     * Test that a GET request to /api/v3/group-types returns an index of all group types.
     *
     * @return void
     */
    public function testGroupTypeIndex()
    {
        factory(GroupType::class, 5)->create();

        $response = $this->getJson('/api/v3/group-types');

        $response->assertOk();

        $this->assertEquals('meta.pagination.count', 5);
    }

    /**
     * Test that a GET request to /api/v3/group-types/:id returns the intended group type.
     *
     * @return void
     */
    public function testGroupTypeShow()
    {
        factory(GroupType::class, 5)->create();

        $groupType = factory(GroupType::class)->create();

        $response = $this->getJson('/api/v3/group-types/' . $groupType->id);

        $response->assertOk();

        $this->assertEquals('data.id', $groupType->id);
    }
}
