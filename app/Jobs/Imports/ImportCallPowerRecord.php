<?php

namespace App\Jobs\Imports;

use App\Auth\Registrar;
use App\Managers\PostManager;
use App\Managers\SignupManager;
use App\Models\Action;
use App\Models\User;
use Exception;
use Chompy\Services\Rogue;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class ImportCallPowerRecord implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    /**
     * The call parameters sent from CallPower.
     *
     * @var array
     */
    protected $parameters;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($parameters)
    {
        $this->parameters = $parameters;
    }

    /**
     * Execute the job to create a CallPower post in Rogue.
     *
     * @return array
     */
    public function handle(
        Registrar $registrar,
        SignupManager $signups,
        PostManager $posts
    ) {
        ['mobile' => $mobile, 'status' => $status] = $this->parameters;

        $details = $this->extractDetails($this->parameters);

        // If this call came from an anonymous mobile, we won't be able to
        // tie it to a user account & so should just discard the record:
        if (is_anonymous_mobile($mobile)) {
            info('Cannot import phone call for anonymous mobile ' . $mobile);

            return;
        }

        // Using the mobile number, get or create a user:
        $user = $registrar->resolve(['mobile' => $mobile]);

        if (!$user) {
            $user = $registrar->register([
                'mobile' => $mobile,
                'sms_status' => 'stop',
            ]);

            info('ImportCallPowerRecord - created user', ['user' => $user->id]);
        }

        // Using the callpower_campaign_id, get the action & campaign:
        $callPowerId = $this->parameters['callpower_campaign_id'];
        $action = Action::fromCallPowerID($callPowerId);

        if (!$action) {
            throw new Exception('Could not find CallPower action.', [
                'callpower_campaign_id' => $callPowerId,
            ]);
        }

        $campaign = $action->campaign;

        info('ImportCallPowerRecord - creating post', [
            'user' => $user->id,
            'details' => $details,
        ]);

        $payload = [
            'northstar_id' => $user->id,
            'action_id' => $action->id,
            'type' => 'phone-call',
            'quantity' => 1,
            'source' => 'importer-client', // @TODO: We should type this.
            'source_details' => 'CallPower',
            'details' => $details,
        ];

        $signup = $signups->get($user->id, $campaign->id);

        if (!$signup) {
            $signup = $signups->create($payload, $user->id, $campaign->id);
        }

        $post = $posts->create($payload, $signup->id);

        if ($post) {
            $post->status = $status === 'completed' ? 'accepted' : 'incomplete';
            $post->save();

            info('ImportCallPowerRecord - creating post', [
                'user' => $user->id,
                'post' => $post->id,
            ]);
        }
    }

    /**
     * Parse the call and return details we want to store in Rogue as a JSON object.
     *
     * @param array $call
     */
    private function extractDetails($call)
    {
        return json_encode([
            'status_details' => $call['status'],
            'call_timestamp' => $call['call_timestamp'],
            'call_duration' => $call['call_duration'],
            'campaign_target_name' => $call['campaign_target_name'],
            'campaign_target_title' => $call['campaign_target_title'],
            'campaign_target_district' => $call['campaign_target_district'],
            'callpower_campaign_name' => $call['callpower_campaign_name'],
            'number_dialed_into' => $call['number_dialed_into'],
        ]);
    }

    /**
     * Returns the parameters passed to this job.
     *
     * @return array
     */
    public function getParameters()
    {
        return $this->parameters;
    }
}
