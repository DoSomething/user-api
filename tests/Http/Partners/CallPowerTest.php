<?php

namespace Tests\Http\Partners;

use App\Jobs\Imports\ImportCallPowerRecord;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

class CallPowerTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        config(['auth.partners.callpower' => 'CallPowerSecretToken!']);
    }

    /**
     * It should kick off a job for a valid payload.
     *
     * @return void
     */
    public function testProcessesCalls()
    {
        Bus::fake();

        $response = $this->postJson(
            'api/partners/callpower/call',
            [
                'mobile' => '2224567891',
                'callpower_campaign_id' => '4',
                'status' => 'completed',
                'call_timestamp' => '2017-11-09 06:34:01.185035',
                'call_duration' => 50,
                'campaign_target_name' => 'Mickey Mouse',
                'campaign_target_title' => 'Representative',
                'campaign_target_district' => 'FL-7',
                'callpower_campaign_name' => 'Test',
                'number_dialed_into' => '+12028519273',
            ],
            [
                'X-DS-CallPower-API-Key' => 'CallPowerSecretToken!',
            ],
        );

        $response->assertOk();

        Bus::assertDispatched(ImportCallPowerRecord::class);
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
            'api/partners/callpower/call',
            [
                // 'mobile' => whoops we forgot this!
                'callpower_campaign_id' => '4',
                'status' => 'completed',
                'call_timestamp' => '2017-11-09 06:34:01.185035',
                // 'call_duration' => who knows?!
                'campaign_target_name' => 'Mickey Mouse',
                'campaign_target_title' => 'Representative',
                'campaign_target_district' => 'FL-7',
                'callpower_campaign_name' => 'Test',
                'number_dialed_into' => '+12028519273',
            ],
            [
                'X-DS-CallPower-API-Key' => 'CallPowerSecretToken!',
            ],
        );

        $response->assertJsonValidationErrors(['mobile', 'call_duration']);

        Bus::assertNotDispatched(ImportCallPowerRecord::class);
    }

    /**
     * It should require an API token.
     *
     * @return void
     */
    public function testRequiresToken()
    {
        Bus::fake();

        $response = $this->postJson('api/partners/callpower/call', [
            'mobile' => '2224567891',
            'callpower_campaign_id' => '4',
            'status' => 'completed',
            'call_timestamp' => '2017-11-09 06:34:01.185035',
            'call_duration' => 50,
            'campaign_target_name' => 'Mickey Mouse',
            'campaign_target_title' => 'Representative',
            'campaign_target_district' => 'FL-7',
            'callpower_campaign_name' => 'Test',
            'number_dialed_into' => '+12028519273',
        ]);

        $response->assertUnauthorized();

        Bus::assertNotDispatched(ImportCallPowerRecord::class);
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
            'api/partners/callpower/call',
            [
                'mobile' => '2224567891',
                'callpower_campaign_id' => '4',
                'status' => 'completed',
                'call_timestamp' => '2017-11-09 06:34:01.185035',
                'call_duration' => 50,
                'campaign_target_name' => 'Mickey Mouse',
                'campaign_target_title' => 'Representative',
                'campaign_target_district' => 'FL-7',
                'callpower_campaign_name' => 'Test',
                'number_dialed_into' => '+12028519273',
            ],
            [
                'X-DS-CallPower-API-Key' => 'let-me-in!!!!', // <-- shockingly, not the token!
            ],
        );

        $response->assertUnauthorized();

        Bus::assertNotDispatched(ImportCallPowerRecord::class);
    }
}
