<?php

namespace App\Jobs\Imports;

use App\Auth\Registrar;
use App\Models\ImportFile;
use App\Types\ImportType;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\App;

class ImportEmailSubscriptions implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    /**
     * The email to subscribe.
     *
     * @var string
     */
    protected $email;

    /**
     * The first name of the user to subscribe.
     *
     * @var string
     */
    protected $first_name;

    /**
     * The source detail if new user.
     *
     * @var string
     */
    protected $source_detail;

    /**
     * The email subscription topic selected by the user.
     *
     * @var string
     */
    protected $selected_subscription_topic;

    /**
     * Create a new job instance.
     *
     * @param array $record
     * @param ImportFile $importFile
     * @param array $importOptions
     * @return void
     */
    public function __construct($record, ImportFile $importFile, $importOptions)
    {
        $this->email = $record['email'];

        $this->first_name = isset($record['first_name'])
            ? $record['first_name']
            : null;

        $this->source_detail = $importOptions['source_detail'];

        $this->selected_subscription_topic =
            $importOptions['email_subscription_topic'];

        $this->importFile = $importFile;
    }

    /**
     * Execute the job to create or update users by email and set subscription topics.
     *
     * @return array
     */
    public function handle()
    {
        // @TODO: standardize logging for info messages; they seem all over the place!
        info('progress_log: Processing: ' . $this->email);

        $registrar = App::make(Registrar::class);

        $user = $registrar->resolve(['email' => $this->email]);

        $collatedSubscriptionTopics = $this->collateSubscriptionTopics($user);

        $input = [
            'first_name' => $this->first_name,
            'email_subscription_status' => true, // @TODO: feels weird this is a boolean.
            'email_subscription_topics' => $collatedSubscriptionTopics,
        ];

        if ($user) {
            // Update the user, filtering out null input values.
            $registrar->register(array_filter($input), $user);

            info('Subscribed existing user', ['user' => $user->id]);
        } else {
            // We need to pass a "source" in order to save through the source_detail.
            $revisedInput = array_merge($input, [
                'email' => $this->email,
                'source' => 'northstar',
                'source_detail' => $this->source_detail,
            ]);

            // Create new user record.
            $user = $registrar->register($revisedInput);

            info('Subscribed new user', ['user' => $user->id]);

            $this->sendPasswordReset($user);
        }

        $this->importFile->incrementImportCount();
    }

    /**
     * Collect, combine and de-duplicate email subscription topics.
     *
     * @return array
     */
    private function collateSubscriptionTopics($user)
    {
        if (!$user) {
            return [$this->selected_subscription_topic];
        }

        $existingTopics = !empty($user->email_subscription_topics)
            ? $user->email_subscription_topics
            : [];

        return array_unique(
            array_merge($existingTopics, [$this->selected_subscription_topic]),
        );
    }

    /**
     * Send Northstar user a password reset email.
     *
     * @param object $user
     */
    private function sendPasswordReset($user)
    {
        $logParams = ['user' => $user->id];

        [
            'enabled' => $topicResetEnabled,
            'type' => $topicResetType,
        ] = $this->getResetConfig();

        $logParams['type'] = $topicResetType;

        if ($topicResetEnabled !== 'true') {
            info(
                'Reset email is disabled, but reset email would have been triggered.',
                $logParams,
            );

            return;
        }

        $user->sendPasswordReset($topicResetType);

        info('Sent reset email', $logParams);
    }

    /**
     * Get the specified import config.
     *
     * @return array
     */
    private function getResetConfig()
    {
        $target = ImportType::getConfig(ImportType::$emailSubscription);

        $key = "topics.$this->selected_subscription_topic.reset";

        return data_get($target, $key);
    }
}
