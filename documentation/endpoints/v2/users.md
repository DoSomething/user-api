# User Endpoints

## Retrieve All Users

Get data for all users in a paginated format. This requires the `user` scope and either the `admin` scope, or "admin" or "staff" role with the appropriate scope. The `write` scope is required for create/update/delete endpoints.

```
GET /v2/users
```

**Additional Query Parameters:**

- `pagination`: **[Experimental]** Either "standard" or "cursor". Cursor pagination is _significantly_ faster, but does not provide any information on the total number of results (only whether another page exists).
- `limit`: Set the number of results to include per page. Default is 20. Maximum is 100.
- `page`: Set the page number to get results from.
- `before`: Filter the collection to include _only_ users with timestamps before the given date or datetime. For example, `/v2/users?before[created_at]=1/1/2015`.
- `after`: Filter the collection to include _only_ users with timestamps after the given date or datetime. For example, `/v2/users?after[created_at]=1/1/2015`.
- `filter`: Filter the collection to include _only_ users matching the following comma-separated values. For example, `/v2/users?filter[drupal_id]=10123,10124,10125` would return users whose Drupal ID is either 10123, 10124, or 10125. You can filter by one or more indexed fields.
- `search`: Search the collection for users with fields whose value match the query. You can search by `id`, `drupal_id`, `email`, and `mobile`. For example, you can search with or without specifying an indexed column: `/v2/users?search[email]=test@example.org` or `v2/users?search=clee@dosomething.org`. Both ways are now supported.

<details>
<summary><strong>Example Request</strong></summary>

```sh
curl -X GET \
  -H "Authorization: Bearer ${ACCESS_TOKEN}" \
  https://northstar.dosomething.org/v2/users?limit=15&page=1
```

</details>

<details>
<summary><strong>Example Response</strong></summary>

```js
// 200 OK

{
    "data": [
        {
            "id": "5480c950bffebc651c8b456f",
            "email": "test@dosomething.org",
            // ...the rest of the user data...
        },
        // etc...
    ],
    "meta": {
        "pagination": [
            "total": 65,
            "count": 20,
            "per_page": 15,
            "current_page": 1,
            "total_pages": 5,
            "links": {
                "next": "https://northstar.dosomething.org/v2/users?page=2",
            }
        ]
    }
}
```

</details>

<details>
<summary><strong>Example Request (filtered)</strong></summary>

```sh
curl -X GET \
  -H "Authorization: Bearer ${ACCESS_TOKEN}" \
  https://northstar.dosomething.org/v2/users?filter[drupal_id]=10010
```

</details>

<details>
<summary><strong>Example Response (filtered)</strong></summary>

```js
// 200 OK

{
    "data": [
        {
            "id": "5480c950bffebc651c8b456f",
            "email": "test@dosomething.org",
            // ...the rest of the user data...
        }
    ],
    "meta": {
        "pagination": [
            "total": 1,
            "count": 1,
            "per_page": 20,
            "current_page": 1,
            "total_pages": 1,
            "links": {}
        ]
    }
}
```

</details>

## Create a User

Create a new user. If a user already exists, an exception error (`A record matching one of the given indexes already exists.` will be returned) and will not create a duplicate account.

An "[upsert](https://docs.mongodb.org/v2.6/reference/glossary/#term-upsert)" will only occur if using the `upsert=true` param. If this is present and a user with a matching identifier is found, new/changed properties will be merged into the existing document. This means
making the same request multiple times will _not_ create duplicate accounts.

Index fields (such as `email`, `mobile`, `drupal_id`) can _only_ be "upserted" if they are not already saved on the user's
account. To change an existing value for one of these fields, you must explicitly update that user via the
[update](#update-a-user) endpoint.

This requires either the `admin` scope, or "admin" or "staff" role with the appropriate scope.

```
POST /v2/users
```

**Request Parameters:**

Either a mobile number or email is required.

```js
// Content-Type: application/json

{
  // Required if 'mobile' or 'facebook_id' is not provided
  email: String;

  // Required if 'email' or 'facebook_id' is not provided
  mobile: String;

  // Required if 'email' or 'mobile' is not provided
  facebook_id: Number;

  // Optional, but required for user to be able to log in!
  password: String;

  // Optional:
  birthdate: Date;
  first_name: String;
  last_name: String;
  addr_street1: String;
  addr_street2: String;
  addr_city: String;
  addr_state: String;
  addr_zip: String;
  country: String; // two character country code
  language: String;
  club_id: Number;
  agg_id: Number;
  cgg_id: Number;
  slack_id: String;
  interests: String, Array; // CSV values or array will be appended to existing interests
  sms_status: String; // Either 'active', 'stop', less', 'undeliverable', 'pending', or 'unknown'
  sms_paused: Boolean; // Whether a user is in a support conversation.
  sms_subscription_topics: Array; // Valid values: 'general', voting'
  email_subscription_status: Boolean; // Whether a user is subscribed to receive emails.
  email_subscription_topics: Array; // Valid values: 'news', 'scholarships', 'lifestyle', 'community', 'clubs'
  source: String; // Immutable. Will only be set on new records.
  source_detail: String; // Only accepted alongside a valid 'source'.
  created_at: Number; // timestamp

  // Hidden fields (optional):
  race: String;
  religion: String;
  college_name: String;
  degree_type: String;
  major_name: String;
  hs_gradyear: String;
  hs_name: String;
  sat_math: Number;
  sat_verbal: Number;
  sat_writing: Number;
}
```

**Additional Query Parameters:**

- `upsert`: Should this request upsert an existing account, if matched? Defaults to `true`.

<details>
<summary><strong>Example Request</strong></summary>

```sh
curl -X POST \
  -H "Authorization: Bearer ${ACCESS_TOKEN}" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"email": "test@example.com", "password": "test123", "birthdate": "10/29/1990", "first_name": "test_fname", "interests": "hockeys,kickballs"}' \
  https://northstar.dosomething.org/v2/users
```

</details>

<details>
<summary><strong>Example Response</strong></summary>

```js
// 200 Okay (or) 201 Created

{
    "data": {
        "id": "555b9225bffebc31068b4567",
        "email": "test",
        "birthdate": "10/29/1990",
        "first_name": "test_fname",
        "interests": [
            "hockeys",
            "kickballs"
        ],
        "role": "user",
        "updated_at": "2016-02-25T19:33:24+0000",
        "created_at": "2016-02-25T18:33:24+0000"
    }
}
```

</details>

## Retrieve a User

Get profile data for a specific user by the user's Northstar ID.

Fetching a user requires either the `admin` scope, or an "admin" or "staff" role with the appropriate scope.

```
GET /v2/users/:user_id
```

<details>
<summary><strong>Example Request</strong></summary>
```sh
curl -X GET \
  -H "Authorization: Bearer ${ACCESS_TOKEN}" \
  -H "Accept: application/json"
  https://northstar.dosomething.org/v2/users/5430e850dt8hbc541c37tt3d
```
</details>

<details>
<summary><strong>Example Response</strong></summary>

```js
// 200 OK

{
    "data": {
        "id": "5430e850dt8hbc541c37tt3d",
        "email": "test@example.com",
        "mobile": "5555555555",
        "facebook_id": "10101010101010101",
        "addr_street1": "123",
        "addr_street2": "456",
        "addr_city": "Paris",
        "addr_state": "Florida",
        "addr_zip": "555555",
        "country": "US",
        "birthdate": "12/17/91",
        "first_name": "First",
        "last_name": "Last",
        "voter_registration_status": "register-form",
        "role": "user",
        "updated_at": "2016-02-25T19:33:24+0000",
        "created_at": "2016-02-25T19:33:24+0000"
    }
}
```

## Retrieve a User By Mobile

Get profile data for a specific user by the user's mobile number.

Fetching a user requires either the `admin` scope, or an "admin" or "staff" role with the appropriate scope.

```
GET /v2/mobile/:mobile
```

<details>
<summary><strong>Example Request</strong></summary>
```sh
curl -X GET \
  -H "Authorization: Bearer ${ACCESS_TOKEN}" \
  -H "Accept: application/json"
  https://northstar.dosomething.org/v2/mobile/5555555555
```
</details>

<details>
<summary><strong>Example Response</strong></summary>

```js
// 200 OK

{
    "data": {
        "id": "5430e850dt8hbc541c37tt3d",
        "email": "test@example.com",
        "mobile": "5555555555",
        "facebook_id": "10101010101010101",
        "addr_street1": "123",
        "addr_street2": "456",
        "addr_city": "Paris",
        "addr_state": "Florida",
        "addr_zip": "555555",
        "country": "US",
        "birthdate": "12/17/91",
        "first_name": "First",
        "last_name": "Last",
        "role": "user",
        "updated_at": "2016-02-25T19:33:24+0000",
        "created_at": "2016-02-25T19:33:24+0000"
    }
}
```

## Retrieve a User By Email

Get profile data for a specific user by the user's email address.

Fetching a user requires either the `admin` scope, or an "admin" or "staff" role with the appropriate scope.

```
GET /v2/email/:email
```

<details>
<summary><strong>Example Request</strong></summary>
```sh
curl -X GET \
  -H "Authorization: Bearer ${ACCESS_TOKEN}" \
  -H "Accept: application/json"
  https://northstar.dosomething.org/v2/email/test@example.com
```
</details>

<details>
<summary><strong>Example Response</strong></summary>

```js
// 200 OK

{
    "data": {
        "id": "5430e850dt8hbc541c37tt3d",
        "email": "test@example.com",
        "mobile": "5555555555",
        "facebook_id": "10101010101010101",
        "addr_street1": "123",
        "addr_street2": "456",
        "addr_city": "Paris",
        "addr_state": "Florida",
        "addr_zip": "555555",
        "country": "US",
        "birthdate": "12/17/91",
        "first_name": "First",
        "last_name": "Last",
        "role": "user",
        "updated_at": "2016-02-25T19:33:24+0000",
        "created_at": "2016-02-25T19:33:24+0000"
    }
}
```

</details>

## Update a User

Update a user resource, retrieved with the user's Northstar ID. This requires either the `admin` scope, or "admin" or "staff" role with the appropriate scope.

```
PUT /v2/users/:user_id
```

**Request Parameters:**

```js
// Content-Type: application/json

{
  email: String;
  mobile: String;
  facebook_id: Number;
  password: String;
  birthdate: Date;
  first_name: String;
  last_name: String;
  addr_street1: String;
  addr_street2: String;
  addr_city: String;
  addr_state: String;
  addr_zip: String;
  country: String; // two character country code
  language: String;
  club_id: Number;
  agg_id: Number;
  cgg_id: Number;
  slack_id: String;
  interests: String, Array; // CSV values or array will be appended to existing interests
  role: String; // Can only be modified by admins. Either 'user' (default), 'staff', or 'admin'.
  sms_status: String; // Either 'active', 'stop', less', 'undeliverable', 'pending', or 'unknown'
  sms_paused: Boolean; // Whether a user is in a support conversation.
  promotions_muted_at: Date; // Used to delete the user's Customer.io profile.

  // Hidden fields (optional):
  race: String;
  religion: String;
  college_name: String;
  degree_type: String;
  major_name: String;
  hs_gradyear: String;
  hs_name: String;
  sat_math: Number;
  sat_verbal: Number;
  sat_writing: Number;
}
```

<details>
<summary><strong>Example Request</strong></summary>

```sh
curl -X PUT \
  -H "Authorization: Bearer ${ACCESS_TOKEN}" \
  -d '{"first_name": "New First name"}' \
  https://northstar.dosomething.org/v2/5430e850dt8hbc541c37tt3d
```

</details>

<details>
<summary><strong>Example Response</strong></summary>

```js
// 200 Okay

{
    "data": {
        "id": "5430e850dt8hbc541c37tt3d",
        "first_name": "New First Name",
        // the rest of the profile...
    }
}
```

</details>

## Update a User's Cause Preferences

Update a user resource's cause preferences, retrieved with the user's Northstar ID. This requires the `user` scope and the `write` scope.

```
POST /v2/users/:user_id/causes/:cause
```

<details>
<summary><strong>Example Request</strong></summary>

```sh
curl -X POST \
  -H "Authorization: Bearer ${ACCESS_TOKEN}" \
  https://northstar.dosomething.org/v2/5430e850dt8hbc541c37tt3d/causes/bullying
```

</details>

<details>
<summary><strong>Example Response</strong></summary>

```js
// 200 Okay

{
    "data": {
        "id": "5430e850dt8hbc541c37tt3d",
        "causes": ["bullying"],
        // the rest of the profile...
    }
}
```

</details>

```
DELETE /v2/users/:user_id/causes/:cause
```

<details>
<summary><strong>Example Request</strong></summary>

```sh
curl -X DELETE \
  -H "Authorization: Bearer ${ACCESS_TOKEN}" \
  https://northstar.dosomething.org/v2/5430e850dt8hbc541c37tt3d/causes/bullying
```

</details>

<details>
<summary><strong>Example Response</strong></summary>

```js
// 200 Okay

{
    "data": {
        "id": "5430e850dt8hbc541c37tt3d",
        "causes": [],
        // the rest of the profile...
    }
}
```

</details>

## Update a User's Email Subscriptions

Update a user resource's email subscriptions, retrieved with the user's Northstar ID. This requires the `user` scope and the `write` scope.

```
POST /v2/users/:user_id/subscriptions/:topic
```

<details>
<summary><strong>Example Request</strong></summary>

```sh
curl -X POST \
  -H "Authorization: Bearer ${ACCESS_TOKEN}" \
  https://northstar.dosomething.org/v2/5430e850dt8hbc541c37tt3d/subscriptions/news
```

</details>

<details>
<summary><strong>Example Response</strong></summary>

```js
// 200 Okay

{
    "data": {
        "id": "5430e850dt8hbc541c37tt3d",
        "email_subscription_topics": [
            "news",
            "lifestyle"
        ],
        // the rest of the profile...
    }
}
```

</details>

```
DELETE /v2/users/:user_id/subscriptions/:topic
```

<details>
<summary><strong>Example Request</strong></summary>

```sh
curl -X DELETE \
  -H "Authorization: Bearer ${ACCESS_TOKEN}" \
  https://northstar.dosomething.org/v2/5430e850dt8hbc541c37tt3d/subscriptions/news
```

</details>

<details>
<summary><strong>Example Response</strong></summary>

```js
// 200 Okay

{
    "data": {
        "id": "5430e850dt8hbc541c37tt3d",
        "email_subscription_topics": [
            "lifestyle"
        ],
        // the rest of the profile...
    }
}
```

</details>

## Delete a User

Destroy a user resource. The `user_id` property of the user to delete must be provided in the URL path, and refers to the user's Northstar ID. This requires either the `admin` scope, or "admin" or "staff" role with the appropriate scope.

```
DELETE /v2/users/:user_id
```

<details>
<summary><strong>Example Request</strong></summary>

```sh
curl -X DELETE \
  -H "Authorization: Bearer ${ACCESS_TOKEN}" \
  https://northstar.dosomething.org/v2/users/555b9ca8bffebc30068b456e
```

</details>

<details>
<summary><strong>Example Response</strong></summary>

```js
// 200 OK

{
    "success": {
        "code": 200,
        "message": "No Content."
    }
}
```

</details>

## Notes

- Northstar will automatically set the `email_subscription_status` field to `true` if a user is created or updated with one or more `email_subscription_topics`.

- Northstar will automatically set the `email_subscription_topics` field to an empty array if a user is updated with a `email_subscription_status` value of `false`.
