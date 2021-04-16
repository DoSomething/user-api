<?php

namespace App\Imports;

use App\ImportType;
use App\SmsStatus;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
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

    /**
     * Parses values to send to DS API from given CSV record, using given config.
     *
     * @param array $record
     * @param array $config
     */
    public function __construct($record, $config = null)
    {
        $validator = Validator::make($record, [
            static::$startedRegistrationFieldName => 'required|date',
        ]);

        if ($validator->fails()) {
            $errorMessages = $validator->errors()->all();

            info(
                'Invalid Rock The Vote record',
                array_merge(
                    [
                        static::$startedRegistrationFieldName =>
                            $record[static::$startedRegistrationFieldName],
                    ],
                    $errorMessages,
                ),
            );

            throw ValidationException::withMessages($errorMessages);
        }

        if (!$config) {
            $config = ImportType::getConfig(ImportType::$rockTheVote);
        }

        $rtvStatus = $this->parseVoterRegistrationStatus(
            $record['Status'],
            $record['Finish with State'],
        );

        $this->userData = [
            'addr_zip' => $record['Home zip code'],
            'email' => $record['Email address'],
            // Source is required in order to set the source detail.
            'source' => config(
                'services.northstar.client_credentials.client_id',
            ),
            'source_detail' => $config['user']['source_detail'],
            'voter_registration_status' => Str::contains($rtvStatus, 'register')
                ? 'registration_complete'
                : $rtvStatus,
        ];

        /**
         * At step 1, a user has only provided their email and zip, but Rock The Vote will sometimes
         * mysteriously send through data for fields populated in later steps. We don't want to save
         * any other data until the status is at least step 2.
         * @see /docs/imports/README.md#status
         */
        if ($rtvStatus !== 'step-1') {
            $emailOptIn = str_to_boolean($record['Opt-in to Partner email?']);

            $this->userData = array_merge($this->userData, [
                'addr_street1' => $record['Home address'],
                'addr_street2' => $record['Home unit'],
                'addr_city' => $record['Home city'],
                'email_subscription_status' => $emailOptIn,
                'email_subscription_topics' => $emailOptIn
                    ? explode(',', $config['user']['email_subscription_topics'])
                    : [],
                'first_name' => $record['First name'],
                'last_name' => $record['Last name'],
                'mobile' =>
                    isset($record[static::$mobileFieldName]) &&
                    is_phone_number($record[static::$mobileFieldName])
                        ? $record[static::$mobileFieldName]
                        : null,
            ]);

            // If a mobile was provided, set SMS subscription per opt-in value.
            if ($this->userData['mobile']) {
                $smsOptIn = str_to_boolean($record[static::$smsOptInFieldName]);

                $this->userData['sms_status'] = $smsOptIn
                    ? SmsStatus::$active
                    : SmsStatus::$stop;
                $this->userData['sms_subscription_topics'] = $smsOptIn
                    ? explode(',', $config['user']['sms_subscription_topics'])
                    : [];
            }
        }

        $this->postData = [
            'source' => $config['post']['source'],
            'source_details' => null,
            'details' => $this->parsePostDetails($record),
            'status' => $rtvStatus,
            'type' => $config['post']['type'],
            'action_id' => $config['post']['action_id'],
        ];

        $trackingSource = $this->parseTrackingSource(
            $record['Tracking Source'],
        );

        $this->userData['id'] = $trackingSource['user_id'];
        $this->userData['referrer_user_id'] =
            $trackingSource['referrer_user_id'];
        $this->postData['group_id'] = $trackingSource['group_id'];
        $this->postData['referrer_user_id'] =
            $trackingSource['referrer_user_id'];
    }

    /**
     * Parses User ID or Referrer User ID from input value.
     * Editors may manually enter this value as a URL query string, so we safety check for typos.
     *
     * @param string $trackingSource
     * @return array
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
                /**
                 * If referral parameter is set to true, the user parameter belongs to the referring
                 * user, not the user that should be associated with this voter registration record.
                 *
                 * Expected key: "referral"
                 */
            } elseif (
                ($key === 'referral' || $key === 'refferal') &&
                str_to_boolean($value[1])
            ) {
                /**
                 * Return result to force querying for existing user via this record email or mobile
                 * upon import.
                 */
                $result['referrer_user_id'] = $userId; // @TODO: error! This would never be set... wtf?!

                return $result;
            }
        }

        $result['user_id'] = isset($userId) ? $userId : null;

        return $result;
    }

    /**
     * Translate a status from Rock The Vote into a Rogue post status.
     *
     * @param  string $rtvStatus
     * @param  string $rtvFinishWithState
     * @return string
     */
    private function parseVoterRegistrationStatus(
        $rtvStatus,
        $rtvFinishWithState
    ) {
        $rtvStatus = strtolower($rtvStatus);

        if ($rtvStatus === 'complete') {
            return str_to_boolean($rtvFinishWithState)
                ? 'register-OVR'
                : 'register-form';
        }

        return str_replace(' ', '-', $rtvStatus);
    }

    /**
     * Parse the record for extra details and return them as a JSON object.
     *
     * @param  array $record
     * @return string
     */
    private function parsePostDetails($record)
    {
        $result = [];

        foreach (config('import.rock_the_vote.post.details') as $key) {
            $result[$key] = $record[$key];
        }

        return json_encode($result);
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
}
