<?php

namespace App\Imports;

use App\Types\ImportType;
use App\Types\SmsStatus;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class RockTheVoteRecord
{
    /**
     * @var string
     */
    public static $mobileFieldName = 'Phone';

    /**
     * Note: Not a typo, this column name does not have the trailing question mark.
     *
     * @var string
     */
    public static $smsOptInFieldName = 'Opt-in to Partner SMS/robocall';

    /**
     * @var string
     */
    public static $startedRegistrationFieldName = 'Started registration';

    public array $config;

    public array $postData;

    public array $trackingSource;

    public array $userData;

    /**
     * Parses values to send to create a RTV Record from given CSV payload data,
     * and provided config values.
     *
     * @param array $payload
     * @param array $config
     */
    public function __construct($payload, $config = null)
    {
        $this->validate($payload);

        $this->config =
            $config ?? ImportType::getConfig(ImportType::$rockTheVote);

        $rtvStatus = $this->parseVoterRegistrationStatus(
            $payload['Status'],
            $payload['Finish with State'],
        );

        // Used in UserData and PostData.
        $this->trackingSource = $this->parseTrackingSource(
            $payload['Tracking Source'],
        );

        $this->setUserData($payload, $rtvStatus);

        $this->setPostData($payload, $rtvStatus);
    }

    /**
     * Set payload data that will be used to create a new post for record if post
     * does not already exist.
     *
     * @param array $payload
     * @param string $status
     */
    private function setPostData($payload, $status)
    {
        $this->postData = [
            'action_id' => $this->config['post']['action_id'],
            'details' => $this->parsePostDetails($payload),
            'source' => $this->config['post']['source'],
            'source_details' => null,
            'status' => $status,
            'type' => $this->config['post']['type'],
        ];

        $this->postData['group_id'] = $this->trackingSource['group_id'];

        $this->postData['referrer_user_id'] =
            $this->trackingSource['referrer_user_id'];
    }

    /**
     * Set payload data that will be used to find or create a new user for the record.
     *
     * @param array $payload
     * @param string $status
     * @return void
     */
    private function setUserData($payload, $status)
    {
        $this->userData = [
            'addr_zip' => $payload['Home zip code'],
            'email' => $payload['Email address'],
            'mobile' => null,
            'sms_status' => null,
            // Source is required in order to set the source detail.
            'source' => 'northstar',
            'source_detail' => $this->config['user']['source_detail'],
            'voter_registration_status' => Str::contains($status, 'register')
                ? 'registration_complete'
                : $status,
        ];

        $this->userData['id'] = $this->trackingSource['user_id'];

        $this->userData['referrer_user_id'] =
            $this->trackingSource['referrer_user_id'];

        //  At step 1, a user has only provided their email and zip, but Rock The Vote will
        //  sometimes mysteriously send through data for fields populated in later steps.
        //  We do not want to save any other data until the status is at least step 2.
        //  @see /docs/imports/README.md#status
        if ($status === 'step-1') {
            return;
        }

        $emailOptIn = str_to_boolean($payload['Opt-in to Partner email?']);

        $this->userData = array_merge($this->userData, [
            'addr_street1' => $payload['Home address'],
            'addr_street2' => $payload['Home unit'],
            'addr_city' => $payload['Home city'],
            'email_subscription_status' => $emailOptIn,
            'email_subscription_topics' => $emailOptIn
                ? explode(
                    ',',
                    $this->config['user']['email_subscription_topics'],
                )
                : [],
            'first_name' => $payload['First name'],
            'last_name' => $payload['Last name'],
            'mobile' =>
                isset($payload[static::$mobileFieldName]) &&
                is_phone_number($payload[static::$mobileFieldName])
                    ? $payload[static::$mobileFieldName]
                    : null,
        ]);

        // If a mobile was provided, set SMS subscription per opt-in value.
        if ($this->userData['mobile']) {
            $smsOptIn = str_to_boolean($payload[static::$smsOptInFieldName]);

            $this->userData['sms_status'] = $smsOptIn
                ? SmsStatus::$active
                : SmsStatus::$stop;

            $this->userData['sms_subscription_topics'] = $smsOptIn
                ? explode(',', $this->config['user']['sms_subscription_topics'])
                : [];
        }
    }

    /**
     * Validate the specified record data.
     *
     * @throws Illuminate\Validation\ValidationException
     */
    private function validate($data)
    {
        $startedRegField = static::$startedRegistrationFieldName;

        $validator = Validator::make($data, [
            $startedRegField => 'required|date',
        ]);

        if ($validator->fails()) {
            $errorMessages = $validator->errors()->all();

            $context = array_merge(
                [
                    $startedRegField => $data[$startedRegField],
                ],
                $errorMessages,
            );

            info('RockTheVoteRecord - invalid record', $context);

            throw ValidationException::withMessages($errorMessages);
        }
    }

    /**
     * Returns decoded post details as an array.
     *
     * @return array
     */
    public function getPostDetails()
    {
        return get_object_vars(json_decode($this->postData['details']));
    }

    /**
     * Parses User ID or Referrer User ID from input value.
     * Editors may manually enter this value as a URL query string, so we safety check for typos.
     *
     * @param string $trackingSource
     * @return array
     * @todo refactor
     */
    public function parseTrackingSource($trackingSource)
    {
        $result = [
            'group_id' => null,
            'referrer_user_id' => null,
            'user_id' => null,
        ];

        if (empty($trackingSource)) {
            return $result;
        }

        $trackingSource = explode(',', $trackingSource);

        foreach ($trackingSource as $value) {
            // See if we are dealing with ":" or "="
            if (Str::contains($value, ':')) {
                $value = explode(':', $value);
            } elseif (Str::contains($value, '=')) {
                $value = explode('=', $value);
            }

            $key = strtolower($value[0]);

            // Expected key: "user"
            if ($key === 'user' || $key === 'user_id' || $key === 'userid') {
                $userId = $value[1];
            } elseif ($key === 'group_id') {
                $result['group_id'] = (int) $value[1];
            } elseif (
                //  If referral parameter is set to true, the user parameter belongs to the referring
                //  user, not the user that should be associated with this voter registration record.
                //  Expected key: "referral"
                ($key === 'referral' || $key === 'refferal') &&
                str_to_boolean($value[1])
            ) {
                // Return result to force querying for existing user via this record email or mobile
                // upon import.
                $result['referrer_user_id'] = $userId; // @TODO: error! This would never be set... wtf?!

                return $result;
            }
        }

        $result['user_id'] = isset($userId) ? $userId : null;

        return $result;
    }

    /**
     * Translate a status from Rock The Vote into a DoSomething post status.
     *
     * @param  string $status
     * @param  string $finishWithState
     * @return string
     */
    private function parseVoterRegistrationStatus($status, $finishWithState)
    {
        $status = strtolower($status);

        if ($status === 'complete') {
            return str_to_boolean($finishWithState)
                ? 'register-OVR'
                : 'register-form';
        }

        return str_replace(' ', '-', $status);
    }

    /**
     * Parse the record payload for extra details and return them as a JSON object.
     *
     * @param  array $payload
     * @return string
     */
    private function parsePostDetails($payload)
    {
        $result = [];

        foreach (config('import.rock_the_vote.post.details') as $key) {
            $result[$key] = $payload[$key];
        }

        return json_encode($result);
    }
}
