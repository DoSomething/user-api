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

    /**
     * Test that when an action is created with an impact goal it saves properly
     *
     * @return void
     */
    public function testCreatingAnActionWithAnImpactGoal()
    {
        $action = factory(Action::class)->create(['impact_goal' => 3000]);

        $response = $this->getJson('api/v3/actions/' . $action->id);

        $response->assertOk();

        $this->assertMysqlDatabaseHas('actions', [
            'post_type' => $action->post_type,
            'name' => $action->name,
            'id' => $action->id,
            'impact_goal' => $action->impact_goal,
        ]);
    }
}
