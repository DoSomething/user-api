<?php

namespace Northstar\Http\Transformers;

use Northstar\Models\User;
use League\Fractal\TransformerAbstract;

class UserInfoTransformer extends TransformerAbstract
{
    /**
     * @param User $user
     * @return array
     */
    public function transform(User $user)
    {
        // User data, formatted according to OpenID Connect spec, section 5.3.
        // @see http://openid.net/specs/openid-connect-core-1_0.html#UserInfo
        $response = [
            'id' => $user->_id,
            'given_name' => $user->first_name,
            'family_name' => $user->last_name,
            'email' => $user->email,
            'phone_number' => $user->mobile,

            'address' => [
                'street_address' => implode(PHP_EOL, [$user->addr_street1, $user->addr_street2]),
                'locality' => $user->addr_city,
                'region' => $user->addr_state,
                'postal_code' => $user->addr_zip,
                'country' => $user->country,
            ],

            'updated_at' => $user->updated_at->timestamp,
            'created_at' => $user->created_at->timestamp,
        ];

        return $response;
    }
}
