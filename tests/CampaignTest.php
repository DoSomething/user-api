<?php

use Northstar\Models\User;
use Northstar\Models\Campaign;

class CampaignTest extends TestCase
{
    protected $phoenixMock;

    protected $server;
    protected $signedUpServer;
    protected $reportedBackServer;
    protected $userScopeKeyServer;

    /**
     * Migrate database and set up HTTP headers
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        // Migrate & seed database
        Artisan::call('migrate');
        $this->seed();

        // Prepare server headers
        $this->server = [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_Accept' => 'application/json',
            'HTTP_X-DS-Application-Id' => '456',
            'HTTP_X-DS-REST-API-Key' => 'abc4324',
            'HTTP_Session' => User::find('5430e850dt8hbc541c37tt3d')->login()->key,
        ];

        $this->signedUpServer = [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_Accept' => 'application/json',
            'HTTP_X-DS-Application-Id' => '456',
            'HTTP_X-DS-REST-API-Key' => 'abc4324',
            'HTTP_Session' => User::find('5480c950bffebc651c8b456f')->login()->key,
        ];

        $this->reportedBackServer = [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_Accept' => 'application/json',
            'HTTP_X-DS-Application-Id' => '456',
            'HTTP_X-DS-REST-API-Key' => 'abc4324',
            'HTTP_Session' => User::find('bf1039b0271bcc636aa5477a')->login()->key,
        ];

        $this->userScopeKeyServer = [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_Accept' => 'application/json',
            'HTTP_X-DS-Application-Id' => '123',
            'HTTP_X-DS-REST-API-Key' => '5464utyrs',
            'HTTP_Session' => User::find('5430e850dt8hbc541c37tt3d')->login()->key,
        ];

        // Mock Phoenix Drupal API class
        $this->phoenixMock = $this->mock('Northstar\Services\Phoenix');
    }

    /**
     * Test for retrieving a user's campaigns
     * GET /users/:term/:id/campaigns
     *
     * @return void
     */
    public function testGetCampaignsFromUser()
    {
        $response = $this->call('GET', 'v1/users/email/test@dosomething.org/campaigns', [], [], [], $this->server);
        $content = $response->getContent();

        // The response should return a 200 OK status code
        $this->assertEquals(200, $response->getStatusCode());

        // Response should be valid JSON
        $this->assertJson($content);
    }

    /**
     * Test for retrieving a user's activity on a single campaign
     * GET /user/campaigns/:campaign_id
     *
     * @return void
     */
    public function testGetSingleCampaignFromUser()
    {
        $response = $this->call('GET', 'v1/user/campaigns/123', [], [], [], $this->signedUpServer);
        $content = $response->getContent();

        // The response should return a 200 OK status code
        $this->assertEquals(200, $response->getStatusCode());

        // Response should be valid JSON
        $this->assertJson($content);
    }

    /**
     * Test for submiting a campaign signup
     * POST /user/campaigns/:nid/signup
     *
     * @return void
     */
    public function testSubmitCampaignSignup()
    {
        $payload = [
            'source' => 'test',
        ];

        // Mock successful response from Drupal API
        $this->phoenixMock->shouldReceive('campaignSignup')->once()->andReturn(100);

        $response = $this->call('POST', 'v1/user/campaigns/123/signup', [], [], [], $this->server, json_encode($payload));
        $content = $response->getContent();
        $data = json_decode($content, true);

        // The response should return a 201 Created status code
        $this->assertEquals(201, $response->getStatusCode());

        // Response should be valid JSON
        $this->assertJson($content);

        // Response should return created at and sid columns
        $this->assertArrayHasKey('created_at', $data['data']);
        $this->assertArrayHasKey('signup_id', $data['data']);
    }

    /**
     * Test for forwarding a campaign signup from phoenix -> northstar
     * POST /forwardSignup
     *
     * @return void
     */
    public function testForwardedCampaignSignup()
    {
        $payload = [
            'source' => 'test',
            'user_drupal_id' => '100001', // seed user 5430e850dt8hbc541c37tt3d
            'campaign_drupal_id' => '100',
            'signup_drupal_id' => '100',
        ];

        $response = $this->call('POST', 'v1/forwardSignup', [], [], [], $this->server, json_encode($payload));
        $content = json_decode($response->getContent(), true);
        $data = $content['data'];

        // Assert response is 201 and has expected data
        $this->assertEquals(201, $response->getStatusCode());
        $this->assertEquals($payload['signup_drupal_id'], $data['signup_id']);
        $this->assertEquals($payload['campaign_drupal_id'], $data['drupal_id']);
        $this->assertEquals($payload['source'], $data['signup_source']);
    }

    /**
     * Test that admin scope is required to complete forwarded signup
     * POST /forwardSignup
     *
     * @return void
     */
    public function testForwardedCampaignSignupRequiresAdminScope()
    {
        $payload = [
            'source' => 'test',
            'user_drupal_id' => '100001',
            'campaign_drupal_id' => '100',
            'signup_drupal_id' => '100',
        ];

        // Test request using an API key without admin scope
        $response = $this->call('POST', 'v1/forwardSignup', [], [], [], $this->userScopeKeyServer, json_encode($payload));

        // Assert response throws a permission error
        $this->assertEquals(403, $response->getStatusCode());
    }

    /**
     * Test for submitting a duplicate campaign signup
     * POST /user/campaigns/:nid/signup
     *
     * @return void
     */
    public function testDuplicateCampaignSignup()
    {
        $payload = ['source' => 'test'];

        $response = $this->call('POST', 'v1/user/campaigns/123/signup', [], [], [], $this->signedUpServer, json_encode($payload));
        $content = $response->getContent();
        $data = json_decode($content, true);

        // Verify a 200 status code
        $this->assertEquals(200, $response->getStatusCode());

        // Verify the signup_id is the same as what was already there
        $this->assertEquals(100, $data['data']['signup_id']);
    }

    /**
     * Test for submitting a new campaign report back.
     * POST /user/campaigns/:nid/reportback
     *
     * @return void
     */
    public function testSubmitCampaignReportback()
    {
        $payload = [
            'quantity' => 10,
            'why_participated' => 'I love helping others',
            'file' => 'data:image/jpeg;base64,/9j/4AAQSkZJRgABAQAAAQABAAD/2wCEAAMCA',
            'caption' => 'Here I am helping others.',
        ];

        // Mock successful response from Drupal API
        $this->phoenixMock->shouldReceive('campaignReportback')->once()->andReturn(100);

        $response = $this->call('POST', 'v1/user/campaigns/123/reportback', [], [], [], $this->signedUpServer, json_encode($payload));
        $content = $response->getContent();
        $data = json_decode($content, true);

        // The response should return a 201 Created status code
        $this->assertEquals(201, $response->getStatusCode());

        // Response should be valid JSON
        $this->assertJson($content);

        // Response should return created at and rbid columns
        $this->assertArrayHasKey('reportback_id', $data['data']);
        $this->assertEquals(100, $data['data']['reportback_id']);
    }

    /**
     * Test for successful update of an existing campaign report back.
     * PUT /user/campaigns/:nid/reportback
     *
     * @return void
     */
    public function testUpdateCampaignReportback200()
    {
        $payload = [
            'quantity' => 10,
            'why_participated' => 'I love helping others',
            'file' => 'data:image/jpeg;base64,/9j/4AAQSkZJRgABAQAAAQABAAD/2wCEAAMCA',
            'caption' => 'Here I am helping others.',
        ];

        // Mock successful response from Drupal API
        $this->phoenixMock->shouldReceive('campaignReportback')->once()->andReturn(100);

        $response = $this->call('PUT', 'v1/user/campaigns/123/reportback', [], [], [], $this->reportedBackServer, json_encode($payload));
        $content = $response->getContent();
        $data = json_decode($content, true);

        // The response should return a 200 Success status code
        $this->assertEquals(200, $response->getStatusCode());

        // Response should be valid JSON
        $this->assertJson($content);

        // Response should return created at and rbid columns
        $this->assertArrayHasKey('reportback_id', $data['data']);
        $this->assertEquals(100, $data['data']['reportback_id']);
    }

    /**
     * Test for creating a reportback when signup does not exist.
     * PUT /user/campaigns/:nid/reportback
     *
     * @return void
     */
    public function testUpdateCampaignReportback401()
    {
        $payload = [
            'quantity' => 10,
            'why_participated' => 'I love helping others',
            'file' => 'data:image/jpeg;base64,/9j/4AAQSkZJRgABAQAAAQABAAD/2wCEAAMCA',
            'caption' => 'Here I am helping others.',
        ];

        $response = $this->call('POST', 'v1/user/campaigns/123/reportback', [], [], [], $this->server, json_encode($payload));
        $content = $response->getContent();

        // Response should return a 501
        $this->assertEquals(401, $response->getStatusCode());

        // Response should be valid JSON
        $this->assertJson($content);
    }
}
