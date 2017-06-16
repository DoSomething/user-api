# Profile Endpoints

## Get Authenticated User's Profile
Get profile data for the [currently authenticated user](../authentication.md).

```
GET /v1/profile
```

<details>
<summary><strong>Example Request</strong></summary>

```sh
curl -X GET \
  -H "Authorization: Bearer ${ACCESS_TOKEN}" \
  -H "Accept: application/json"
  https://northstar.dosomething.org/v1/profile
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
        "updated_at": "2015-05-19 19:03:21",
        "created_at": "2015-05-19 15:47:08"
    }
}
```
</details>

## Update Authenticated User's Profile
Update the profile data for the [currently authenticated user](../authentication.md).

```
POST /v1/profile
```

**Request Parameters:**

```js
// Content-Type: application/json

{
  email: String
  mobile: String
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
  parse_installation_ids: String // CSV values or array will be appended to existing interests
  interests: String, Array // CSV values or array will be appended to existing interests
  source: String // Immutable (can only be set if existing value is `null`)
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
<summary><strong>Example Request</strong></summary>

```sh
curl -X POST \
  -H "Authorization: Bearer ${ACCESS_TOKEN}" \
  -d '{"first_name": "New First name"}' \
  https://northstar.dosomething.org/v1/profile
```
</details>

<details>
<summary><strong>Example Response</strong></summary>

```js
// 200 OK

{
    "data": {
        "id": "5430e850dt8hbc541c37tt3d",
        "first_name": "New First Name",
        // the rest of the profile...
    }
}
```
</details>

## Get Authenticated User's Signups
Hmm, documentation is coming soon! :construction:

## Get Authenticated User's Reportbacks
Hmm, documentation is coming soon! :construction:
