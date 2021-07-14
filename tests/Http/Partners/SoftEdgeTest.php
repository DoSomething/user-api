<?php

namespace Tests\Http\Partners;

use App\Jobs\Imports\ImportSoftEdgeRecord;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

class SoftEdgeTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        config(['auth.partners.softedge' => 'SoftEdgeSecretToken!']);
    }

    /**
     * It should kick off a job for a valid payload.
     *
     * @return void
     */
    public function testProcessesEmails()
    {
        Bus::fake();

        $response = $this->postJson(
            'api/partners/softedge/email',
            [
                'action_id' => '123',
                'northstar_id' => '609d4e6cc166170977230222',
                'email_timestamp' => '2017-11-07 18:54:10.829655',
                'campaign_target_name' => 'Contact Mickey Mouse',
                'campaign_target_title' => 'Representative',
                'campaign_target_district' => 'FL-7',
            ],
            [
                'X-DS-SoftEdge-API-Key' => 'SoftEdgeSecretToken!',
            ],
        );

        $response->assertOk();

        Bus::assertDispatched(ImportSoftEdgeRecord::class);
    }

    /**
     * It should validate required keys are provided.
     *
     * @return void
     */
    public function testValidatesRequestPayload()
    {
        Bus::fake();

        $response = $this->postJson(
            'api/partners/softedge/email',
            [
                // 'action_id' => whoops, forgot!
                'northstar_id' => '609d4e6cc166170977230222',
                // 'email_timestamp' => did it even happen? we'll never know!
                'campaign_target_name' => 'Contact Mickey Mouse',
                'campaign_target_title' => 'Representative',
                'campaign_target_district' => 'FL-7',
            ],
            [
                'X-DS-SoftEdge-API-Key' => 'SoftEdgeSecretToken!',
            ],
        );

        $response->assertJsonValidationErrors(['action_id', 'email_timestamp']);

        Bus::assertNotDispatched(ImportSoftEdgeRecord::class);
    }

    /**
     * It should require an API token.
     *
     * @return void
     */
    public function testRequiresToken()
    {
        Bus::fake();

        $response = $this->postJson('api/partners/softedge/email', [
            'action_id' => '123',
            'northstar_id' => '609d4e6cc166170977230222',
            'email_timestamp' => '2017-11-07 18:54:10.829655',
            'campaign_target_name' => 'Contact Mickey Mouse',
            'campaign_target_title' => 'Representative',
            'campaign_target_district' => 'FL-7',
        ]);

        $response->assertUnauthorized();

        Bus::assertNotDispatched(ImportSoftEdgeRecord::class);
    }

    /**
     * It should require the correct partner API token.
     *
     * @return void
     */
    public function testRequiresValidToken()
    {
        Bus::fake();

        $response = $this->postJson(
            'api/partners/softedge/email',
            [
                'action_id' => '123',
                'northstar_id' => '609d4e6cc166170977230222',
                'email_timestamp' => '2017-11-07 18:54:10.829655',
                'campaign_target_name' => 'Contact Mickey Mouse',
                'campaign_target_title' => 'Representative',
                'campaign_target_district' => 'FL-7',
            ],
            [
                'X-DS-SoftEdge-API-Key' => 'let-me-in!!!!', // <-- shockingly, not the token!
            ],
        );

        $response->assertUnauthorized();

        Bus::assertNotDispatched(ImportSoftEdgeRecord::class);
    }
}
