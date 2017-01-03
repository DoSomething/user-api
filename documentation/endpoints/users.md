# User Endpoints
## Retrieve All Users
Get data for all users in a paginated format. This requires either the `admin` scope, or "admin" or "staff" role with the appropriate scope.


```
GET /v1/users
```

**Additional Query Parameters:**

- `limit`: Set the number of results to include per page. Default is 20. Maximum is 100.
- `page`: Set the page number to get results from.
- `filter`: Filter the collection to include _only_ users matching the following comma-separated values. For example, `/v1/users?filter[drupal_id]=10123,10124,10125` would return users whose Drupal ID is either 10123, 10124, or 10125. You can filter by one or more indexed fields.
- `search`: Search the collection for users with fields whose value match the query. For example, `/v1/users?search[id]=test@example.com&search[email]=test@example.org` would return all users with either an ID or email address matching `test@example.org`. You can search by one or more indexed fields.

<details>
<summary>**Example Request**</summary>

```sh
curl -X GET \
  -H "Authorization: Bearer ${ACCESS_TOKEN}" \
  https://northstar.dosomething.org/v1/users?limit=15&page=1
```

</details>

<details>
<summary>**Example Response**</summary>

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
<summary>**Example Request (filtered)**</summary>

```sh
curl -X GET \
  -H "Authorization: Bearer ${ACCESS_TOKEN}" \
  https://northstar.dosomething.org/v1/users?filter[drupal_id]=10010
```

</details>

<details>
<summary>**Example Response (filtered)**</summary>

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
  email: String

  // Required if 'email' or 'facebook_id' is not provided
  mobile: String

  // Required if 'email' or 'mobile' is not provided
  facebook_id: Number

  // Optional, but required for user to be able to log in!
  password: String

  // Optional:
  birthdate: Date
  first_name: String
  last_name: String
  addr_street1: String
  addr_street2: String
  addr_city: String
  addr_state: String
  addr_zip: String
  country: String // two character country code
  language: String
  agg_id: Number
  cgg_id: Number
  drupal_id: String
  slack_id: String
  parse_installation_ids: String // CSV values or array will be appended to existing interests
  interests: String, Array // CSV values or array will be appended to existing interests
  source: String // Will only be set on new records, or if being provided an earlier `created_at`.
  created_at: Number // timestamp

  // Hidden fields (optional):
  race: String
  religion: String
  college_name: String
  degree_type: String
  major_name: String
  hs_gradyear: String
  hs_name: String
  sat_math: Number
  sat_verbal: Number
  sat_writing: Number
}
```

**Additional Query Parameters:**

- `upsert`: Should this request upsert an existing account, if matched? Defaults to `true`.

<details>
<summary>**Example Request**</summary>

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
<summary>**Example Response**</summary>

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
<summary>**Example Request**</summary>
```sh
curl -X GET \
  -H "Authorization: Bearer ${ACCESS_TOKEN}" \
  -H "Accept: application/json"
  https://northstar.dosomething.org/v1/users/mobile/5555555555
```
</details>

<details>
<summary>**Example Response**</summary>

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
  email: String
  mobile: String
  facebook_id: Number
  password: String
  birthdate: Date
  first_name: String
  last_name: String
  addr_street1: String
  addr_street2: String
  addr_city: String
  addr_state: String
  addr_zip: String
  country: String // two character country code
  language: String
  agg_id: Number
  cgg_id: Number
  drupal_id: String
  slack_id: String
  parse_installation_ids: String // CSV values or array will be appended to existing interests
  interests: String, Array // CSV values or array will be appended to existing interests
  role: String // Can only be modified by admins. Either 'user' (default), 'staff', or 'admin'.

  // Hidden fields (optional):
  race: String
  religion: String
  college_name: String
  degree_type: String
  major_name: String
  hs_gradyear: String
  hs_name: String
  sat_math: Number
  sat_verbal: Number
  sat_writing: Number
}
```

<details>
<summary>**Example Request**</summary>

```sh
curl -X PUT \
  -H "Authorization: Bearer ${ACCESS_TOKEN}" \
  -d '{"first_name": "New First name"}' \
  https://northstar.dosomething.org/v1/_id/5430e850dt8hbc541c37tt3d
```

</details>

<details>
<summary>**Example Response**</summary>

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
<summary>**Example Request**</summary>

```sh
curl -X DELETE \
  -H "Authorization: Bearer ${ACCESS_TOKEN}" \
  https://northstar.dosomething.org/v1/users/555b9ca8bffebc30068b456e
```

</details>

<details>
<summary>**Example Response**</summary>

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

## Set User Avatar
Save an avatar to the user's Northstar profile. Accepts a file or Base64 string in the data request. This will return
the updated User profile document, with a `photo` attribute pointing to the newly created image.

```
POST /v1/users/:user_id/avatar
```

**Request Parameters:**
```js
// Content-Type: multipart/form-data --or-- application/json
// Accept: application/json

{
  /* Required */
  photo: File or String
}
```

<details>
<summary>**Example Request**</summary>

```sh
curl -X POST \
  -H "Authorization: Bearer ${ACCESS_TOKEN}" \
  -H "Content-Type: multipart-form-data: \
  -H "Accept: application/json" \
  -d '{"photo": "profile_pic.jpeg"}' \
  https://northstar.dosomething.org/v1/users/{id}/avatar
```

</details>

<details>
<summary>**Example Response**</summary>

```js
// 200 OK

{
    "data": {
        "id": "5430e850dt8hbc541c37tt3d",
        "photo": "https://avatar.dosomething.org/uploads/avatars/55566327bffebc0b3e8b45a5-1456498835.jpeg"
        // the rest of the user object...
        "updated_at": "2016-02-25T18:33:25+0000"
    }
}
```

</details>
