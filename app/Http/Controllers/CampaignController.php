<?php

namespace Northstar\Http\Controllers;

use Illuminate\Http\Request;
use Northstar\Events\UserSignedUp;
use Northstar\Events\UserReportedBack;
use Northstar\Models\Campaign;
use Northstar\Models\User;
use Northstar\Services\Phoenix;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CampaignController extends Controller
{
    /**
     * Phoenix Drupal API wrapper.
     * @var Phoenix
     */
    protected $phoenix;

    public function __construct(Phoenix $drupal)
    {
        $this->phoenix = $drupal;

        $this->middleware('key:user');
        $this->middleware('key:admin', ['only' => 'forwardSignup']);
        $this->middleware('auth', ['except' => 'forwardSignup']);

        $this->middleware('campaign');
    }

    /**
     * Returns a user's campaigns
     * GET /users/:term/:id/campaigns
     *
     * @param $term string - Term to search by (eg. mobile, drupal_id, id, etc)
     * @param $id   string - The value to search for
     *
     * @return \Illuminate\Http\Response
     * @throws NotFoundHttpException
     */
    public function index($term, $id)
    {
        // Find the user.
        $user = User::where($term, $id)->first();

        if (! $user) {
            throw new NotFoundHttpException('The resource does not exist.');
        }

        $campaigns = $user->campaigns;

        foreach ($campaigns as $campaign) {
            if ($campaign->reportback_id) {
                $response = $this->phoenix->reportbackContent($campaign->reportback_id);

                // Possible for reportback data to be missing if it's been deleted on Drupal
                if (isset($response['data'])) {
                    $campaign['reportback_data'] = $response['data'];
                }
            }
        }

        return $this->respond($campaigns);
    }

    /**
     * Display the specified campaign.
     * GET /campaigns/:campaign_id
     *
     * @param int $campaign_id - Campaign ID
     *
     * @return \Illuminate\Http\Response
     * @throws NotFoundHttpException
     */
    public function show($campaign_id)
    {
        $user = User::current();

        $campaign = $user->campaigns()->where('drupal_id', $campaign_id)->first();

        if (! $campaign) {
            throw new NotFoundHttpException('User has not signed up for this campaign.');
        }

        if ($campaign->reportback_id) {
            $response = $this->phoenix->reportbackContent($campaign->reportback_id);

            if (isset($response['data'])) {
                $campaign['reportback_data'] = $response['data'];
            }
        }

        return $this->respond($campaign);
    }

    /**
     * Sign user up for a given campaign.
     * POST /campaigns/:campaign_id/signup
     *
     * @param $campaign_id - Drupal campaign node ID
     * @param Request $request
     *
     * @return \Illuminate\Http\Response
     * @throws HttpException
     */
    public function signup($campaign_id, Request $request)
    {
        // Validate request body
        $this->validate($request, [
            'source' => ['required'],
        ]);

        // Get the currently authenticated Northstar user.
        $user = User::current();

        // Return an error if the user doesn't exist.
        if (! $user->drupal_id) {
            throw new HttpException(401, 'The user must have a Drupal ID to sign up for a campaign.');
        }

        // Check if campaign signup already exists.
        $campaign = $user->campaigns()->where('drupal_id', $campaign_id)->first();

        $statusCode = 200;
        if (! $campaign) {
            $statusCode = 201;

            // Create a Drupal signup via Drupal API, and store signup ID in Northstar.
            $signup_id = $this->phoenix->campaignSignup($user->drupal_id, $campaign_id, $request->input('source'));

            // Save reference to the signup on the user object.
            $campaign = new Campaign;
            $campaign->drupal_id = $campaign_id;
            $campaign->signup_id = $signup_id;
            $campaign->signup_source = $request->input('source');
            // If group is specified, use that. Otherwise, use the signup_id.
            $campaign->signup_group = $request->input('group') ?: $signup_id;
            $campaign = $user->campaigns()->save($campaign);

            // Fire sign up event.
            event(new UserSignedUp($user, $campaign));
        }

        return $this->respond($campaign, $statusCode);
    }

    /**
     * Forward user's campaign signup from Phoenix to sync campaign activity.
     * POST /forwardSignup
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\Response
     * @throws HttpException
     */
    public function forwardSignup(Request $request)
    {
        // Validate request body
        $this->validate($request, [
            'source' => ['required'],
            'user_drupal_id' =>['required'],
            'campaign_drupal_id' => ['required'],
            'signup_drupal_id' => ['required']
        ]);

        // Get the provided Northstar user by Drupal ID.
        $user = User::where('drupal_id', $request->input('user_drupal_id'))->first();

        // Return an error if the user doesn't exist.
        if (!$user) {
            throw new HttpException(401, 'The user must have a Drupal ID to sign up for a campaign.');
        }

        // Check if campaign signup already exists.
        $campaign = $user->campaigns()->where('drupal_id', $request->input('campaign_drupal_id'))->first();

        $statusCode = 200;
        if(! $campaign) {
            $statusCode = 201;

            $campaign = new Campaign;
            $campaign->drupal_id = $request->input('campaign_drupal_id');
            $campaign->signup_id = $request->input('signup_drupal_id');
            $campaign->signup_source = $request->input('source');

            // If group is specified, use that. Otherwise, use the signup_id.
            $campaign->signup_group = $request->input('group') ?: $request->input('signup_drupal_id');

            // Save to user.
            $campaign = $user->campaigns()->save($campaign);

            // Fire sign up event only if `event` is set in payload.
            // e.g., if "backfilling" old signups, we don't want to spam push notifications.
            if($request->input('event')) {
                event(new UserSignedUp($user, $campaign));
            }
        }

        return $this->respond($campaign, $statusCode);
    }

    /**
     * Store a newly created campaign report back in storage.
     * POST /campaigns/:campaign_id/reportback
     * PUT  /campaigns/:campaign_id/reportback
     *
     * @param $campaign_id - Drupal campaign node ID
     * @param Request $request
     *
     * @return \Illuminate\Http\Response
     * @throws HttpException
     */
    public function reportback($campaign_id, Request $request)
    {
        // Validate request body
        $this->validate($request, [
            'quantity' => ['required', 'integer'],
            'why_participated' => ['required'],
            'file' => ['required', 'string'], // Data URL!
            'caption' => ['string'],
            'source' => ['string'],
        ]);

        // Get the currently authenticated Northstar user.
        $user = User::current();

        // Return an error if the user doesn't exist.
        if (! $user->drupal_id) {
            throw new HttpException(401, 'The user must have a Drupal ID to submit a reportback.');
        }

        // Check if campaign signup already exists.
        $campaign = $user->campaigns()->where('drupal_id', $campaign_id)->first();

        if (! $campaign) {
            throw new HttpException(401, 'User is not signed up for this campaign yet.');
        }

        // Create a reportback via the Drupal API, and store reportback ID in Northstar
        $reportback_id = $this->phoenix->campaignReportback($user->drupal_id, $campaign_id, $request->all());

        // Set status code based on whether `reportback_id` field already exists or not
        $statusCode = 201;
        if ($campaign->reportback_id) {
            $statusCode = 200;
        }

        $campaign->reportback_id = $reportback_id;

        if ($request->has('source')) {
            $campaign->reportback_source = $request->input('source');
        }

        $campaign->save();

        // Fire reportback event.
        event(new UserReportedBack($user, $campaign));

        return $this->respond($campaign, $statusCode);
    }
}
