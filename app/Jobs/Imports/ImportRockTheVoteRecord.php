<?php

namespace App\Jobs\Imports;

use App\Auth\Registrar;
use App\Imports\RockTheVoteRecord;
use App\Jobs\Job;
use App\Managers\PostManager;
use App\Managers\SignupManager;
use App\Models\Action;
use App\Models\ImportFile;
use App\Models\Post;
use App\Models\RockTheVoteLog;
use App\Models\User;
use App\Types\ImportType;
use App\Types\PostType;
use App\Types\SmsStatus;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;

class ImportRockTheVoteRecord extends Job
{
    protected array $config;

    protected string $exception;

    /**
     * @var \App\Models\ImportFile
     */
    protected $importFile;

    /**
     * The mobile user (if different from "primary" user).
     *
     * @var \App\Models\User
     */
    protected $mobileUser;

    /**
     * A single record parsed from a row in the Rock the Vote csv.
     *
     * @var \App\Imports\RockTheVoteRecord
     */
    protected $record;

    /**
     * @var \App\Auth\Registrar
     */
    protected $registrar;

    /**
     * Create a new job instance.
     *
     * @param array $payload
     * @param ImportFile $importFile
     * @return void
     */
    public function __construct($payload, ImportFile $importFile)
    {
        $this->config = ImportType::getConfig(ImportType::$rockTheVote);

        $this->importFile = $importFile;

        try {
            $this->record = new RockTheVoteRecord($payload, $this->config);
        } catch (ValidationException $e) {
            $this->exception = $e->getMessage();

            return;
        }
    }

    /**
     * Execute the job to upsert a user and their voter registration post.
     *
     * @return array
     */
    public function handle(
        PostManager $posts,
        Registrar $registrar,
        SignupManager $signups
    ) {
        if (!$this->record && $this->exception) {
            $this->importFile->incrementSkipCount();

            return [
                'response' => ['message' => $this->exception],
            ];
        }

        $this->registrar = $registrar;

        $user = $this->getUser();

        if (!$user) {
            return $this->importRecordAsNewUser($signups, $posts);
        }

        if (RockTheVoteLog::getByRecord($this->record, $user)) {
            $this->skipImportingRecord($user);
        }

        $this->importRecordForExistingUser($user, $signups, $posts);
    }

    /**
     * When running this job synchronously (e.g. ImportFileController), we return an
     * array of context/results from the 'handle' method to aid with testing.
     *
     * @param \App\Models\User $user
     * @param \App\Models\Post $post
     * @return array
     */
    private function formatResponse(User $user, Post $post)
    {
        $userFields = [
            'id',
            'email_preview',
            'mobile_preview',
            'voter_registration_status',
            'referrer_user_id',
            'sms_status',
            'sms_subscription_topics',
            'email_subscription_status',
            'email_subscription_topics',
        ];

        return [
            'record.userData' => $this->record->userData,
            'record.postData' => $this->record->postData,
            'user' => Arr::only($user->toArray(), $userFields),
            'mobile_user' => !empty($this->mobileUser)
                ? Arr::only($this->mobileUser->toArray(), $userFields)
                : null,
            'post' => Arr::only($post->toArray(), [
                'id',
                'type',
                'action_id',
                'status',
                'details',
                'referrer_user_id',
                'group_id',
            ]),
        ];
    }

    /**
     * If user exists, get user by id, or by email or by mobile.
     *
     * @param array $userData
     * @return \App\Models\User|null
     */
    private function getUser()
    {
        $userData = $this->record->userData;

        if ($userData['id']) {
            $user = $this->registrar->resolve(['id' => $userData['id']]);

            if ($user) {
                info('Found user by id: ', ['user' => $user->id]);
            }

            return $user;
        }

        if ($userData['email']) {
            $user = $this->registrar->resolve([
                'email' => $userData['email'],
            ]);

            if ($user) {
                info('Found user by email: ', ['user' => $user->id]);
            }

            return $user;
        }

        if ($userData['mobile']) {
            $user = $this->registrar->resolve([
                'mobile' => $userData['mobile'],
            ]);

            if ($user) {
                info('Found user by mobile: ', ['user' => $user->id]);
            }

            return $user;
        }

        return null;
    }

    /**
     * Updates specified user with provided data.
     *
     * @param \App\Models\User $user
     * @param array $data
     * @return
     */
    private function updateUser(User $user, $data)
    {
        if (!$data) {
            return $user;
        }

        $user->fill($data);
        $user->save();

        info('ImportRockTheVoteRecord - updated user', [
            'user' => $user->id,
            'changed' => array_keys($data),
        ]);

        return $user;
    }

    /**
     * Returns a post for specified user if found, and the import record
     * "Started registration" date matches the date for the post.
     *
     * @param \App\Models\User $user
     * @param \App\Models\Action $action
     * @return \App\Models\Post|null
     */
    public function getPost(User $user, Action $action)
    {
        $posts = Post::where([
            'action_id' => $action->id,
            'northstar_id' => $user->id,
            'type' => config('import.rock_the_vote.post.type'),
        ])->get();

        if ($posts->isEmpty()) {
            return null;
        }

        $key = 'Started registration';

        $importRecordDate = $this->record->getPostDetails()[$key];

        // @TODO: refactor to simplify and utilize Eloquent's helpful firstWhere method.
        // @see: https://github.com/DoSomething/northstar/pull/1207#discussion_r636337846
        foreach ($posts as $post) {
            if (!isset($post['details'])) {
                continue;
            }

            $details = json_decode($post['details']);

            if ($details->{$key} === $importRecordDate) {
                return $post;
            }
        }

        return null;
    }

    /**
     * Create post with import record data.
     *
     * @param \App\Models\User $user
     * @param \App\Models\Action $action
     * @param \App\Managers\SignupManager $signups
     * @param \App\Managers\PostManager $posts
     * @return \App\Models\Post
     */
    private function createPost(
        $user,
        $action,
        SignupManager $signups,
        PostManager $posts
    ) {
        $payload = [
            'action_id' => $action->id,
            'details' => $this->record->postData['details'],
            'northstar_id' => $user->id,
            'source_details' => $this->record->postData['source_details'],
            'type' => (string) PostType::VOTER_REG(),
        ];

        $signup = $signups->get($user->id, $action->campaign->id);

        if (!$signup) {
            $signup = $signups->create(
                $payload,
                $user->id,
                $action->campaign->id,
            );
        }

        $post = $posts->create($payload, $signup->id);

        info('ImportRockTheVoteRecord - created post', [
            'user_id' => $user->id,
            'action_id' => $action->id,
            'post_id' => $post->id,
        ]);

        return $post;
    }

    /**
     * Updates post with import record data if needed.
     *
     * @param \App\Models\Post $post
     * @return array
     */
    private function updatePost(Post $post)
    {
        $shouldUpdateStatus = self::shouldUpdateStatus(
            $post['status'],
            $this->record->postData['status'],
        );

        if (!$shouldUpdateStatus) {
            return $post;
        }

        $post->fill([
            'status' => $this->record->postData['status'],
        ]);

        info('ImportRockTheVoteRecord - updated post', [
            'post_id' => $post->id,
            'status' => $post->status,
        ]);

        return $post;
    }

    /**
     * Import the Rock The Vote record as a new user.
     *
     * @param \App\Managers\SignupManager $signup
     * @param \App\Managers\PostManager $post
     */
    private function importRecordAsNewUser($signups, $posts)
    {
        $user = $this->registrar->register($this->record->userData);

        $action = Action::find($this->record->postData['action_id']);

        $post = $this->createPost($user, $action, $signups, $posts);

        if (!$post) {
            return [];
        }

        RockTheVoteLog::createFromRecord(
            $this->record,
            $user,
            $this->importFile,
        );

        $this->sendPasswordReset($user);

        return $this->formatResponse($user, $post);
    }

    /**
     * Import the Rock The Vote record for existing user.
     *
     * @param \App\Models\User $user
     * @param \App\Managers\SignupManager $signups
     * @param \App\Managers\PostManager $posts
     */
    private function importRecordForExistingUser(
        User $user,
        SignupManager $signups,
        PostManager $posts
    ) {
        $user = $this->updateStatusIfChanged($user);

        // If import record has a mobile number provided, update SMS subscription.
        if (isset($this->record->userData['mobile'])) {
            $user = $this->updateSmsSubscriptionIfChanged($user);
        }

        // @TODO: should this be findOrFail and throw an execption that stops execution?
        $action = Action::find($this->record->postData['action_id']);

        $post = $this->getPost($user, $action);

        $post = $post
            ? $this->updatePost($post)
            : $this->createPost($user, $action, $signups, $posts);

        if (!$post) {
            return [];
        }

        RockTheVoteLog::createFromRecord(
            $this->record,
            $user,
            $this->importFile,
        );

        return $this->formatResponse($user, $post);
    }

    /**
     * Skip importing the record because it has already been added.
     *
     * @param \App\Models\User $user
     * @return
     */
    private function skipImportingRecord(User $user)
    {
        $details = $this->record->getPostDetails();

        $message =
            'ImportRockTheVoteRecord - Skipping record that has already been imported.';

        $data = [
            'user' => $user->id,
            'status' => $details['Status'],
            'started_registration' => $details['Started registration'],
        ];

        info($message, $data);

        $this->importFile->incrementSkipCount();

        return [
            'response' => array_merge(
                ['message' => $message],
                ['keys' => $data],
            ),
        ];
    }

    /**
     * Determines if a current status should be changed to given value.
     *
     * @param string $currentStatus
     * @param string $newStatus
     * @return bool
     */
    public static function shouldUpdateStatus($currentStatus, $newStatus)
    {
        $statusHierarchy = config('import.rock_the_vote.status_hierarchy');

        $indexOfCurrentStatus = array_search($currentStatus, $statusHierarchy);
        $indexOfNewStatus = array_search($newStatus, $statusHierarchy);

        return $indexOfCurrentStatus < $indexOfNewStatus;
    }

    /**
     * Updates user's voter registration status if changed per import.
     *
     * @return \App\Models\User
     */
    public function updateStatusIfChanged(User $user)
    {
        $userData = $this->record->userData;

        $shouldUpdateStatus = self::shouldUpdateStatus(
            $user->voter_registration_status,
            $userData['voter_registration_status'],
        );

        if (!$shouldUpdateStatus) {
            return $user;
        }

        return $this->updateUser($user, [
            'voter_registration_status' =>
                $userData['voter_registration_status'],
        ]);
    }

    /**
     * Update specified user's SMS subscription if changed in import record.
     *
     * @return \App\Models\User
     */
    private function updateSmsSubscriptionIfChanged(User $user)
    {
        // Don't update import user's SMS subscription if we already did for this registration.
        if (RockTheVoteLog::hasUpdatedSmsSubscription($this->record, $user)) {
            return $user;
        }

        // If import user already has a mobile, do not change it, just update subscription.
        if ($user->mobile) {
            return $this->updateUser(
                $user,
                $this->getSmsSubscriptionPayload($user),
            );
        }

        // Check if another user already owns the import mobile.
        $this->mobileUser = $this->registrar->resolve([
            'mobile' => $this->record->userData['mobile'],
        ]);

        dd(['poopie', $this->mobileUser]);

        // If another user owns the import mobile, update their subscription.
        if ($this->mobileUser) {
            $this->mobileUser = $this->updateUser(
                $this->mobileUser,
                $this->getSmsSubscriptionPayload($this->mobileUser),
            );

            return $user;
        }

        // Otherwise, update the import user's mobile and subscription.
        return $this->updateUser(
            $user,
            array_merge(
                ['mobile' => $this->record->userData['mobile']],
                $this->getSmsSubscriptionPayload($user),
            ),
        );
    }

    /**
     * Get fields and values to update given user with if their SMS subscription has changed.
     *
     * @param \App\Models\User $user
     * @return array
     */
    private function getSmsSubscriptionPayload(User $user)
    {
        return array_merge(
            $this->parseSmsStatusChange($user),
            $this->parseSmsSubscriptionTopicsChange($user),
        );
    }

    /**
     * Returns payload to update SMS subscription topics if they have changed.
     *
     * @param \App\Models\User $user
     * @return array
     */
    private function parseSmsSubscriptionTopicsChange(User $user)
    {
        $fieldName = 'sms_subscription_topics';

        $currentSmsTopics = !empty($user->{$fieldName})
            ? $user->{$fieldName}
            : [];

        $updatedSmsTopics = [];

        // If user opted in to SMS, add the import topics to current topics.
        if ($this->optedIntoReceivingSms()) {
            $updatedSmsTopics = array_unique(
                array_merge(
                    $currentSmsTopics,
                    $this->record->userData[$fieldName],
                ),
            );

            // If we didn't add any new topics, nothing to update.
            if (count($updatedSmsTopics) === count($currentSmsTopics)) {
                return [];
            }

            return [$fieldName => $updatedSmsTopics];
        }

        // Nothing to remove if current topics is empty.
        if (!count($currentSmsTopics)) {
            return [];
        }

        // If user hasn't opted-in and has current topics, remove all import topics
        // from the current topics list.
        foreach ($currentSmsTopics as $topic) {
            $rtvTopics = config(
                'import.rock_the_vote.user.sms_subscription_topics',
            );

            if (!in_array($topic, explode(',', $rtvTopics))) {
                array_push($updatedSmsTopics, $topic);
            }
        }

        return [$fieldName => $updatedSmsTopics];
    }

    /**
     * Returns payload to update SMS status if it has changed.
     *
     * @param \App\Models\User $user
     * @return array
     */
    private function parseSmsStatusChange(User $user)
    {
        $fieldName = 'sms_status';
        $currentSmsStatus = $user->{$fieldName};
        $importSmsStatus = $this->record->userData[$fieldName];

        //  If current status is null or undeliverable, update status per whether they opted in
        //  via the RTV form.
        //  This is the only scenario when we want to change an existing user's status to stop.
        if (
            $currentSmsStatus === SmsStatus::$undeliverable ||
            !$currentSmsStatus
        ) {
            return [$fieldName => $importSmsStatus];
        }

        if (
            $this->optedIntoReceivingSms() &&
            in_array($currentSmsStatus, [
                SmsStatus::$less,
                SmsStatus::$pending,
                SmsStatus::$stop,
            ])
        ) {
            return [$fieldName => $importSmsStatus];
        }

        return [];
    }

    /**
     * @return bool
     */
    private function optedIntoReceivingSms()
    {
        return $this->record->userData['sms_status'] === SmsStatus::$active;
    }

    /**
     * Send User a password reset email.
     *
     * @param \App\Models\User $user
     */
    private function sendPasswordReset($user)
    {
        // Our Customer.io event triggered campaign that sends these RTV password resets should be
        // configured to not send the email to an unsubscribed user, but let's sanity check anyway.
        if (!$user->email_subscription_status) {
            info('Did not send email to unsubscribed user', [
                'user' => $user->id,
            ]);

            return;
        }

        $passwordResetType = $this->config['reset']['type'];

        $logParams = ['user' => $user->id, 'type' => $passwordResetType];

        if ($this->config['reset']['enabled'] !== 'true') {
            info(
                'Reset email is disabled. Would have sent reset email',
                $logParams,
            );

            return;
        }

        $user->sendPasswordReset($passwordResetType);

        info('Sent reset email', $logParams);
    }

    /**
     * Returns the record passed to this job.
     *
     * @return array
     */
    public function getParameters()
    {
        return get_object_vars($this->record);
    }
}
