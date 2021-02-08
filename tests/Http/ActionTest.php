<?php

use App\Models\Action;

class ActionTest extends TestCase
{
    /**
     * Test that a GET request to /api/v3/actions returns an index of all actions.
     *
     * GET /api/v3/actions
     * @return void
     */
    public function testActionIndex()
    {
        factory(Action::class, 5)->create();

        $response = $this->getJson('api/v3/actions');

        $response->assertOk();
        $response->assertJsonPath('meta.pagination.count', 5);
    }

    /**
     * Test that a GET request to /api/v3/actions/:action_id returns the intended action.
     *
     * GET /api/v3/actions/:action_id
     * @return void
     */
    public function testActionShow()
    {
        factory(Action::class, 5)->create();

        // Create a specific action to search for.
        $action = factory(Action::class)->create();

        $response = $this->getJson('api/v3/actions/' . $action->id);

        $response->assertOk();
        $response->assertJsonPath('data.id', $action->id);
    }
}
