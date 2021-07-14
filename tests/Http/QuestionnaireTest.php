<?php

namespace Tests\Http;

use App\Models\Action;
use App\Models\Campaign;
use App\Models\Group;
use App\Models\User;
use Tests\TestCase;

class QuestionnaireTest extends TestCase
{
    /**
     * Test that a POST request to /posts creates a new
     * post and signup, if it doesn't already exist.
     *
     * @return void
     */
    public function testCreatingQuestionnairePostsAndSignups()
    {
        $user = factory(User::class)->create();
        $referrerUser = factory(User::class)->create();

        $campaignId = factory(Campaign::class)->create()->id;
        $location = 'US-' . $this->faker->stateAbbr();
        $schoolId = $this->faker->school_id;
        $details = ['source-detail' => 'broadcast-123', 'other' => 'other'];
        $groupId = factory(Group::class)->create()->id;

        $contentfulId = '123';

        $actionOne = factory(Action::class)->create([
            'campaign_id' => $campaignId,
            'post_type' => 'text',
        ]);

        $questionOne = [
            'title' => 'Question one.',
            'answer' => 'Answer one.',
            'action_id' => $actionOne->id,
        ];

        $actionTwo = factory(Action::class)->create([
            'campaign_id' => $campaignId,
            'post_type' => 'text',
        ]);

        $questionTwo = [
            'title' => 'Question two.',
            'answer' => 'Answer two.',
            'action_id' => $actionTwo->id,
        ];

        $response = $this->asUser($user)->json(
            'POST',
            'api/v3/questionnaires',
            [
                'questions' => [$questionOne, $questionTwo],
                'contentful_id' => $contentfulId,
                'location' => $location,
                'school_id' => $schoolId,
                'details' => json_encode($details),
                'referrer_user_id' => $referrerUser->id,
                'group_id' => $groupId,
            ],
        );

        $response->assertStatus(201)->assertJson([
            'data' => [
                [
                    'action_id' => $questionOne['action_id'],
                    'media' => ['text' => $questionOne['answer']],
                ],
                [
                    'action_id' => $questionTwo['action_id'],
                    'media' => ['text' => $questionTwo['answer']],
                ],
            ],
        ]);

        // Make sure the signup is persisted to the database.
        $this->assertMysqlDatabaseHas('signups', [
            'campaign_id' => $campaignId,
            'northstar_id' => $user->id,
            'referrer_user_id' => $referrerUser->id,
            'group_id' => $groupId,
        ]);

        // Make sure two posts are persisted to the database.
        $this->assertMysqlDatabaseHas('posts', [
            'northstar_id' => $user->id,
            'campaign_id' => $campaignId,
            'type' => 'text',
            'action' => $actionOne->name,
            'action_id' => $actionOne->id,
            'text' => $questionOne['answer'],
            'status' => 'pending',
            'location' => $location,
            'school_id' => $schoolId,
            'details' => json_encode(
                array_merge($details, [
                    'questionnaire' => true,
                    'question' => $questionOne['title'],
                    'contentful_id' => $contentfulId,
                ]),
            ),
            'referrer_user_id' => $referrerUser->id,
            'group_id' => $groupId,
        ]);

        $this->assertMysqlDatabaseHas('posts', [
            'northstar_id' => $user->id,
            'campaign_id' => $campaignId,
            'type' => 'text',
            'action' => $actionTwo->name,
            'action_id' => $actionTwo->id,
            'text' => $questionTwo['answer'],
            'status' => 'pending',
            'location' => $location,
            'school_id' => $schoolId,
            'details' => json_encode(
                array_merge($details, [
                    'questionnaire' => true,
                    'question' => $questionTwo['title'],
                    'contentful_id' => $contentfulId,
                ]),
            ),
            'referrer_user_id' => $referrerUser->id,
            'group_id' => $groupId,
        ]);

        // We only want to trigger one customer.io event per questionnaire submission (even though we store two posts).
        $this->assertCustomerIoEvent($user, 'campaign_signup_post')->once();
    }

    /**
     * Test validation for creating a post.
     *
     * POST /api/v3/posts
     * @return void
     */
    public function testCreatingAQuestionnaireWithValidationErrors()
    {
        $user = factory(User::class)->create();
        $action = factory(Action::class)->create([
            'post_type' => 'text',
        ]);

        $response = $this->asUser($user)->postJson('api/v3/questionnaires', [
            'action_id' => $action->id,
            'type' => 'text',
            'questions' => [
                [
                    // Should be a valid Action ID:
                    'action_id' => null,
                    // Should both be Strings:
                    'title' => 1,
                    'answer' => 2,
                ],
            ],
            // and we've omitted the required 'contentful_id' field!
        ]);

        $response->assertJsonValidationErrors([
            'contentful_id',
            'questions.0.action_id',
            'questions.0.title',
            'questions.0.answer',
        ]);
    }

    /**
     * Test that non-authenticated user's/apps can't create a questionnaire.
     *
     * @return void
     */
    public function testUnauthenticatedUserCreatingAQuestionnaire()
    {
        $response = $this->postJson('api/v3/posts', []);

        $response->assertUnauthorized();
    }
}
