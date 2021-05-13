<?php

namespace App\Jobs\Imports;

use App\Managers\PostManager;
use App\Managers\SignupManager;
use App\Models\Action;
use App\Models\User;
use App\Types\PostType;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use InvalidArgumentException;

class ImportSoftEdgeRecord implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    /**
     * The call parameters sent from SoftEdge.
     *
     * @var array
     */
    protected array $parameters;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(array $parameters)
    {
        $this->parameters = $parameters;
    }

    /**
     * Execute the job to create a SoftEdge post.
     *
     * @return array
     */
    public function handle(SignupManager $signups, PostManager $posts)
    {
        $user = User::findOrFail($this->parameters['northstar_id']);
        $action = Action::findOrFail($this->parameters['action_id']);
        $campaign = $action->campaign;
        $details = $this->extractDetails($this->parameters);

        // TODO: We should assert this in the repository!
        if ($action->post_type !== (string) PostType::EMAIL()) {
            throw new InvalidArgumentException(
                'Received SoftEdge import for non-email action.',
            );
        }

        info('ImportSoftEdgeRecord - creating post', [
            'user_id' => $user->id,
            'action_id' => $action->id,
            'details' => $details,
        ]);

        $payload = [
            'northstar_id' => $user->id,
            'action_id' => $action->id,
            'type' => 'email',
            'quantity' => 1,
            'source_details' => 'SoftEdge',
            'details' => $details,
        ];

        $signup = $signups->get($user->id, $campaign->id);

        if (!$signup) {
            $signup = $signups->create($payload, $user->id, $campaign->id);
        }

        $post = $posts->create($payload, $signup->id);

        if ($post) {
            $post->status = 'accepted';
            $post->save();

            info('ImportSoftEdgeRecord - created post', [
                'user_id' => $user->id,
                'action_id' => $action->id,
                'post_id' => $post->id,
            ]);
        }
    }

    /**
     * Parse the call and return details we want to store as a JSON object.
     *
     * @param array $call
     */
    private function extractDetails($email)
    {
        return json_encode([
            'email_timestamp' => $email['email_timestamp'],
            'campaign_target_name' => $email['campaign_target_name'],
            'campaign_target_title' => $email['campaign_target_title'],
            'campaign_target_district' => $email['campaign_target_district'],
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
