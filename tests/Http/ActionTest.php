<?php

namespace Tests\Http;

use App\Models\Action;
use App\Models\Post;
use Tests\TestCase;

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
     * Test that when an action is created with an impact goal it saves properly.
     *
     * @return void
     */
    public function testDisplayingActionWithAnImpactGoal()
    {
        $action = factory(Action::class)->create(['impact_goal' => 3000]);

        $response = $this->getJson('api/v3/actions/' . $action->id);

        $response->assertOk();
        $response->assertJsonPath('data.impact_goal', $action->impact_goal);
    }

    /**
     * Test for action to show with included accepted quantity.
     *
     * GET /api/v3/actions/:action_id?include=accepted_quantity
     * @return void
     */
    public function testActionShowWithAcceptedQuantityAsAdmin()
    {
        $action = factory(Action::class)->create();

        // Create three accepted posts associated to the action and 3 pending.
        factory(Post::class, 3)->create([
            'action_id' => $action->id,
            'status' => 'accepted',
            'quantity' => 25,
        ]);
        factory(Post::class, 3)->create([
            'action_id' => $action->id,
            'status' => 'pending',
            'quantity' => $this->faker->numberBetween(10, 1000),
        ]);

        // Test with admin that only the 3 accepted post's quantities are counted.
        $response = $this->asAdminUser()->getJson(
            'api/v3/actions/' . $action->id . '?include=accepted_quantity',
        );

        $response->assertOk();
        $response->assertJsonPath('data.accepted_quantity.data.quantity', 75);
    }
}
