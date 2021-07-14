<?php

namespace Tests\Http;

use App\Models\Club;
use Tests\TestCase;

class ClubTest extends TestCase
{
    /**
     * Test that a GET request to /api/v3/clubs returns an index of all clubs.
     *
     * @return void
     */
    public function testClubIndex()
    {
        factory(Club::class, 5)->create();

        $response = $this->getJson('/api/v3/clubs');

        $response->assertOk();
        $response->assertJsonPath('meta.pagination.count', 5);
    }

    /**
     * Test that we can filter clubs by name.
     * GET /api/v3/campaigns.
     * @return void
     */
    public function testClubIndexNameFilter()
    {
        $clubNames = [
            'Batman Begins',
            'Bipartisan',
            'Brave New World',
            'If I Never Knew You',
            'San Dimas High School',
            'Santa Claus',
        ];

        foreach ($clubNames as $clubName) {
            factory(Club::class)->create([
                'name' => $clubName,
            ]);
        }

        $responseOne = $this->getJson('/api/v3/clubs?filter[name]=new');

        $responseOne->assertOk();
        $responseOne->assertJsonPath('meta.pagination.count', 2);

        $responseTwo = $this->getJson('/api/v3/clubs?filter[name]=san');

        $responseTwo->assertOk();
        $responseTwo->assertJsonPath('meta.pagination.count', 3);
    }

    /**
     * Test that we can paginate clubs using 'after' cursor.
     * GET /api/v3/campaigns.
     * @return void
     */
    public function testClubIndexAfterCursor()
    {
        $clubOne = factory(Club::class)->create();
        $clubTwo = factory(Club::class)->create();

        $cursor = $clubOne->getCursor();

        $response = $this->getJson("/api/v3/clubs?cursor[after]=$cursor");

        $response->assertOk();
        $response->assertJsonPath('meta.cursor.count', 1);
        $response->assertJsonPath('data.0.id', $clubTwo->id);
    }

    /**
     * Test that a GET request to /api/v3/clubs/:id returns the intended club.
     *
     * @return void
     */
    public function testClubShow()
    {
        factory(Club::class, 5)->create();

        // Create a specific club to search for.
        $club = factory(Club::class)->create();

        $response = $this->getJson("/api/v3/clubs/$club->id");

        $response->assertOk();
        $response->assertJsonPath('data.id', $club->id);
    }
}
