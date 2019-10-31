# User Endpoints

The `write` scope is required for create/update/delete endpoints.

## Retrieve All Users

Get data for all users in a paginated format. This requires the `user` scope and either the `admin` scope, or "admin" or "staff" role with the appropriate scope.

```
GET /v1/users
```

**Additional Query Parameters:**

- `pagination`: **[Experimental]** Either "standard" or "cursor". Cursor pagination is _significantly_ faster, but does not provide any information on the total number of results (only whether another page exists).
- `limit`: Set the number of results to include per page. Default is 20. Maximum is 100.
- `page`: Set the page number to get results from.
- `before`: Filter the collection to include _only_ users with timestamps before the given date or datetime. For example, `/v1/users?before[created_at]=1/1/2015`.
- `after`: Filter the collection to include _only_ users with timestamps after the given date or datetime. For example, `/v1/users?after[created_at]=1/1/2015`.
- `filter`: Filter the collection to include _only_ users matching the following comma-separated values. For example, `/v1/users?filter[drupal_id]=10123,10124,10125` would return users whose Drupal ID is either 10123, 10124, or 10125. You can filter by one or more indexed fields.
- `search`: Search the collection for users with fields whose value match the query. You can search by `id`, `drupal_id`, `email`, and `mobile`. For example, you can search with or without specifying an indexed column: `/v1/users?search[email]=test@example.org` or `v1/users?search=clee@dosomething.org`. Both ways are now supported.

<details>
<summary><strong>Example Request</strong></summary>

```sh
curl -X GET \
  -H "Authorization: Bearer ${ACCESS_TOKEN}" \
  https://northstar.dosomething.org/v1/users?limit=15&page=1
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
                "next": "https://northstar.dosomething.org/v1/users?page=2",
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
  https://northstar.dosomething.org/v1/users?filter[drupal_id]=10010
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
            "drupal_id": "10010",
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

Create a new user. This is performed as an "[upsert](https://docs.mongodb.org/v2.6/reference/glossary/#term-upsert)" by default,
so if a user with a matching identifier is found, new/changed properties will be merged into the existing document. This means
making the same request multiple times will _not_ create duplicate accounts.

Index fields (such as `email`, `mobile`, `drupal_id`) can _only_ be "upserted" if they are not already saved on the user's
account. To change an existing value for one of these fields, you must explicitly update that user via the
[update](#update-a-user) endpoint.

This requires either the `admin` scope, or "admin" or "staff" role with the appropriate scope.

```
POST /v1/users
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
  school_id: String;
  agg_id: Number;
  cgg_id: Number;
  drupal_id: String;
  slack_id: String;
  parse_installation_ids: String; // CSV values or array will be appended to existing interests
  interests: String, Array; // CSV values or array will be appended to existing interests
  sms_status: String; // Either 'active', 'stop', 'less', 'undeliverable', 'pending', or 'unknown'
  sms_paused: Boolean; // Whether a user is in a support conversation.
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
  https://northstar.dosomething.org/v1/users?create_drupal_user=1
```

</details>

<details>
<summary><strong>Example Response</strong></summary>

```js
// 200 Okay (or) 201 Created

{
    "data": {
        "id": "555b9225bffebc31068b4567",
        "_id": "555b9225bffebc31068b4567",
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

Get profile data for a specific user. This can be retrieved with either the user's Northstar ID (which is automatically
generated when a new database record is created), a mobile phone number, an email address, a Facebook ID or the user's Drupal ID.

Fetching a user via username, email, or mobile requires either the `admin` scope, or an "admin" or "staff" role with the appropriate scope.

```
GET /v1/users/id/<user_id>
GET /v1/users/mobile/<mobile>
GET /v1/users/email/<email>
GET /v1/users/drupal_id/<drupal_id>
GET /v1/users/facebook_id/<facebook_id>
```

<details>
<summary><strong>Example Request</strong></summary>
```sh
curl -X GET \
  -H "Authorization: Bearer ${ACCESS_TOKEN}" \
  -H "Accept: application/json"
  https://northstar.dosomething.org/v1/users/mobile/5555555555
```
</details>

<details>
<summary><strong>Example Response</strong></summary>

```js
// 200 OK

{
    "data": {
        "_id": "5430e850dt8hbc541c37tt3d",
        "id": "5430e850dt8hbc541c37tt3d",
        "email": "test@example.com",
        "mobile": "5555555555",
        "facebook_id": "10101010101010101",
        "drupal_id": "123456",
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

Update a user resource. This can be retrieved with the user's Northstar ID or the source ID (`drupal_id`). This requires either the `admin` scope, or "admin" or "staff" role with the appropriate scope.

```
PUT /v1/users/_id/<user_id>
PUT /v1/users/drupal_id/<drupal_id>
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
  agg_id: Number;
  cgg_id: Number;
  drupal_id: String;
  slack_id: String;
  parse_installation_ids: String; // CSV values or array will be appended to existing interests
  interests: String, Array; // CSV values or array will be appended to existing interests
  role: String; // Can only be modified by admins. Either 'user' (default), 'staff', or 'admin'.
  sms_status: String; // Either 'active', 'stop', less', 'undeliverable', 'pending', or 'unknown'
  sms_paused: Boolean; // Whether a user is in a support conversation.
  email_subscription_status: Boolean; // Whether a user can recieve emails or not.

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
  https://northstar.dosomething.org/v1/_id/5430e850dt8hbc541c37tt3d
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

## Delete a User

Destroy a user resource. The `user_id` property of the user to delete must be provided in the URL path, and refers to the user's Northstar ID. This requires either the `admin` scope, or "admin" or "staff" role with the appropriate scope.

```
DELETE /v1/users/:user_id
```

<details>
<summary><strong>Example Request</strong></summary>

```sh
curl -X DELETE \
  -H "Authorization: Bearer ${ACCESS_TOKEN}" \
  https://northstar.dosomething.org/v1/users/555b9ca8bffebc30068b456e
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

## Merge User Accounts

Merge two user accounts. The `id` of the "target" user must be provided in the URL path, and the "duplicate" ID should be provided in the body. This requires either the `admin` scope, or "admin" or "staff" role with the appropriate scope.

:warning: **[Experimental]** This is an experimental endpoint and isn't thoroughly tested.

```
POST /v1/users/:user_id/merge
```

**Additional Query Parameters:**

- `pretend`: Return the result of merging the given accounts, but do not save.

**Request Parameters:**

```js
// Content-Type: application/json
// Accept: application/json

{
  /* Required, duplicate account */
  id: String;
}
```

<details>
<summary><strong>Example Request</strong></summary>

```sh
curl -X POST \
  -H "Authorization: Bearer ${ACCESS_TOKEN}" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"id": "5809387d9a89204ec64b8162"}' \
  https://northstar.dosomething.org/v1/users/5809387d9a89204ec64b8161/merge
```

</details>

<details>
<summary><strong>Example Response</strong></summary>

```js
// 200 OK

{
  "data": {
    "id": "5809387d9a89204ec64b8161",
    "_id": "5809387d9a89204ec64b8161",
    "first_name": "test",
    "last_name": null,
    "last_initial": "",
    "photo": null,
    "email": "test@dosomething.org",
    "mobile": "5554443333",
    "facebook_id": 54,
    "interests": null,
    "birthdate": "1989-04-05",
    "addr_street1": "61237 Olson Lane Apt. 682",
    "addr_street2": null,
    "addr_city": null,
    "addr_state": "CO",
    "addr_zip": "87801-0467",
    "source": "factory",
    "source_detail": null,
    "slack_id": null,
    "mobilecommons_id": null,
    "parse_installation_ids": null,
    "sms_status": null,
    "sms_paused": false,
    "language": null,
    "country": null,
    "drupal_id": "187",
    "role": "user",
    "last_authenticated_at": null,
    "updated_at": "2016-12-07T16:16:41+00:00",
    "created_at": "2014-03-14T15:05:57+00:00"
  },
  "meta": {
    "updated": [
      "mobile"
    ]
  }
}
```

</details>
