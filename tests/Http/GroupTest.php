<?php

namespace Tests\Http;

use App\Models\Group;
use App\Models\GroupType;
use Tests\TestCase;

class GroupTest extends TestCase
{
    /**
     * Test that a GET request to /api/v3/groups returns an index of all group types.
     *
     * @return void
     */
    public function testGroupIndex()
    {
        $groupType = factory(GroupType::class)->create();

        $groupNames = [
            'Batman Begins',
            'Bipartisan',
            'Brave New World',
            'If I Never Knew You',
            'San Dimas High School',
            'Santa Claus',
        ];

        foreach ($groupNames as $groupName) {
            factory(Group::class)->create([
                'group_type_id' => $groupType->id,
                'name' => $groupName,
            ]);
        }

        $responseOne = $this->getJson('/api/v3/groups');

        $responseOne->assertOk();
        $responseOne->assertJsonPath('meta.pagination.count', 6);

        $responseTwo = $this->getJson('/api/v3/groups?filter[name]=new');

        $responseTwo->assertOk();
        $responseTwo->assertJsonPath('meta.pagination.count', 2);

        $responseThree = $this->getJson('/api/v3/groups?filter[name]=san');

        $responseThree->assertOk();
        $responseThree->assertJsonPath('meta.pagination.count', 3);

        // Test for encoded special characters.
        $response = $this->getJson('/api/v3/groups?filter[name]=g%5C');

        $response->assertOk();
        $response->assertJsonPath('meta.pagination.count', 0);
    }

    /**
     * Test that a GET request to /api/v3/groups/:id returns the intended group type.
     *
     * @return void
     */
    public function testGroupShow()
    {
        factory(Group::class, 5)->create();

        // Create a specific group type to search for.
        $group = factory(Group::class)->create();

        $response = $this->getJson("/api/v3/groups/$group->id");

        $response->assertOk();
        $response->assertJsonPath('data.id', $group->id);
    }
}
