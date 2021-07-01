<?php

namespace Tests\Http\Web;

use App\Models\Action;
use App\Models\Campaign;
use App\Models\User;
use Tests\TestCase;

class WebActionTest extends TestCase
{
    /**
     * Test that a POST request to /actions creates a new action.
     *
     * POST /actions
     * @return void
     */
    public function testCreatingAnAction()
    {
        $campaign = factory(Campaign::class)->create();

        $actionName = $this->faker->sentence;

        $admin = factory(User::class)->states('admin')->create();

        $this->actingAs($admin, 'web')->postJson('/admin/actions', [
            'name' => $actionName,
            'campaign_id' => $campaign->id,
            'post_type' => 'photo',
            'action_type' => 'make-something',
            'time_commitment' => '0.5-1.0',
            'reportback' => true,
            'civic_action' => false,
            'scholarship_entry' => true,
            'volunteer_credit' => false,
            'anonymous' => false,
            'impact_goal' => 2000,
            'noun' => 'things',
            'verb' => 'done',
        ]);

        $this->assertMysqlDatabaseHas('actions', [
            'name' => $actionName,
            'campaign_id' => $campaign->id,
        ]);
    }

    /**
     * Test that a POST request to /actions with duplicate data does
     * not create a new action.
     *
     * POST /actions
     * @return void
     */
    public function testCannotCreateADuplicateAction()
    {
        $this->markTestSkipped(
            'System currently allows duplicates and needs to be revisited.',
        );

        $campaign = factory(Campaign::class)->create();

        $actionName = $this->faker->sentence;

        $admin = factory(User::class)->states('admin')->create();

        $actionBody = [
            'name' => $actionName,
            'campaign_id' => $campaign->id,
            'post_type' => 'photo',
            'action_type' => 'make-something',
            'time_commitment' => '0.5-1.0',
            'reportback' => true,
            'civic_action' => false,
            'scholarship_entry' => true,
            'volunteer_credit' => false,
            'anonymous' => false,
            'noun' => 'things',
            'verb' => 'done',
        ];

        $responseOne = $this->actingAs($admin, 'web')->postJson(
            '/admin/actions',
            $actionBody,
        );

        $responseOne->assertCreated();

        // Try to create a second action with the same name, post type,
        // and campaign id to make sure it doesn't duplicate.
        $responseTwo = $this->actingAs($admin, 'web')->postJson(
            '/admin/actions',
            $actionBody,
        );

        $responseTwo->assertStatus(422);
    }

    /**
     * Test that a PATCH request to /actions/:action_id updates an action.
     *
     * PATCH /actions/:action_id
     * @return void
     */
    public function testUpdatingAnAction()
    {
        $admin = factory(User::class)->states('admin')->create();

        $action = factory(Action::class)->create();

        $updatedName = 'Updated Name';

        // Update the name.
        $this->actingAs($admin, 'web')->patchJson(
            "/admin/actions/$action->id",
            [
                'name' => $updatedName,
                'post_type' => $action->post_type,
                'action_type' => $action->action_type,
                'time_commitment' => $action->time_commitment,
                'noun' => $action->noun,
                'verb' => $action->verb,
            ],
        );

        $this->assertMysqlDatabaseHas('actions', [
            'name' => $updatedName,
        ]);
    }

    /**
     * Test that a DELETE request to /actions/:action_id deletes an action.
     *
     * DELETE /actions/:action_id
     * @return void
     */
    public function testDeleteAnAction()
    {
        $admin = factory(User::class)->states('admin')->create();

        $action = factory(Action::class)->create();

        // Delete the action.
        $this->actingAs($admin, 'web')->deleteJson(
            "/admin/actions/$action->id",
        );

        $response = $this->getJson('api/v3/actions/' . $action->id);

        $response->assertNotFound();
    }

    /**
     * Test that a POST request to /actions with invalid data types is not successful.
     *
     * POST /admin/actions
     * @return void
     */
    public function testCannotCreateActionWithInvalidInput()
    {
        $campaign = factory(Campaign::class)->create();

        $actionName = $this->faker->sentence;

        $admin = factory(User::class)->states('admin')->create();

        $response = $this->actingAs($admin, 'web')->post('/admin/actions', [
            'name' => $actionName,
            'campaign_id' => $campaign->id,
            'post_type' => 'photo',
            'action_type' => 'make-something',
            'time_commitment' => '0.5-1.0',
            'reportback' => true,
            'civic_action' => false,
            'scholarship_entry' => true,
            'volunteer_credit' => false,
            'anonymous' => false,
            'impact_goal' => 'dog', //should be an integer
            'noun' => 'things',
            'verb' => 'done',
        ]);

        $response->assertSessionHasErrors([
            'impact_goal' => 'The impact goal must be an integer.',
        ]);
    }
}
