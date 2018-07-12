# Users Resource

{% hint style="warning" %}
The following v1 endpoints have been deprecated. Please use the v2 endpoints.
{% endhint %}

{% api-method method="get" host="https://identity.dosomething.org" path="/v1/users" %}
{% api-method-summary %}
Retrieve all Users
{% endapi-method-summary %}

{% api-method-description %}
Get an index list of all users.
{% endapi-method-description %}

{% api-method-spec %}
{% api-method-request %}

{% api-method-response %}
{% api-method-response-example httpCode=200 %}
{% api-method-response-example-description %}

{% endapi-method-response-example-description %}

```javascript
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
{% endapi-method-response-example %}
{% endapi-method-response %}
{% endapi-method-spec %}
{% endapi-method %}

{% api-method method="get" host="https://identity.dosomething.org" path="/v1/users/:term/:identifier" %}
{% api-method-summary %}
Retrieve a single User
{% endapi-method-summary %}

{% api-method-description %}
Get profile data for a single user using a specified id.
{% endapi-method-description %}

{% api-method-spec %}
{% api-method-request %}
{% api-method-path-parameters %}
{% api-method-parameter name="term" type="string" required=false %}
Type of ID provided.
{% endapi-method-parameter %}

{% api-method-parameter name="identifier" type="string" required=true %}
ID of user based on term.
{% endapi-method-parameter %}
{% endapi-method-path-parameters %}
{% endapi-method-request %}

{% api-method-response %}
{% api-method-response-example httpCode=200 %}
{% api-method-response-example-description %}
User profile successfully retrieved.
{% endapi-method-response-example-description %}

```javascript
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
{% endapi-method-response-example %}
{% endapi-method-response %}
{% endapi-method-spec %}
{% endapi-method %}

{% api-method method="post" host="https://identity.dosomething.org" path="/v1/users" %}
{% api-method-summary %}
Create a User
{% endapi-method-summary %}

{% api-method-description %}
Create  a new user.
{% endapi-method-description %}

{% api-method-spec %}
{% api-method-request %}
{% api-method-body-parameters %}
{% api-method-parameter name="email" type="string" required=false %}
Required if "mobile" or "facebook\_id" is not provided.
{% endapi-method-parameter %}

{% api-method-parameter name="mobile" type="string" required=false %}
Required if "email" or "facebook\_id" is not provided.
{% endapi-method-parameter %}

{% api-method-parameter name="facebook\_id" type="number" required=false %}
Required if "email" or "mobile" is not provided.
{% endapi-method-parameter %}

{% api-method-parameter name="password" type="string" required=false %}
Optional, but required for user to be able to log in.
{% endapi-method-parameter %}

{% api-method-parameter name="birthdate" type="string" required=false %}
Date.
{% endapi-method-parameter %}

{% api-method-parameter name="first\_name" type="string" required=false %}

{% endapi-method-parameter %}

{% api-method-parameter name="last\_name" type="string" required=false %}

{% endapi-method-parameter %}

{% api-method-parameter name="addr\_street1" type="string" required=false %}

{% endapi-method-parameter %}

{% api-method-parameter name="addr\_street2" type="string" required=false %}

{% endapi-method-parameter %}

{% api-method-parameter name="addr\_city" type="string" required=false %}

{% endapi-method-parameter %}

{% api-method-parameter name="addr\_state" type="string" required=false %}

{% endapi-method-parameter %}

{% api-method-parameter name="addr\_zip" type="string" required=false %}

{% endapi-method-parameter %}

{% api-method-parameter name="country" type="string" required=false %}
Two character country code.
{% endapi-method-parameter %}

{% api-method-parameter name="language" type="string" required=false %}

{% endapi-method-parameter %}

{% api-method-parameter name="agg\_id" type="number" required=false %}

{% endapi-method-parameter %}

{% api-method-parameter name="cgg\_id" type="number" required=false %}

{% endapi-method-parameter %}

{% api-method-parameter name="drupal\_id" type="string" required=false %}

{% endapi-method-parameter %}

{% api-method-parameter name="slack\_id" type="string" required=false %}

{% endapi-method-parameter %}

{% api-method-parameter name="parse\_installation\_ids" type="string" required=false %}
CSV values or array will be appended to existing interests.
{% endapi-method-parameter %}

{% api-method-parameter name="interests" type="string" required=false %}
String or Array. CSV values or array will be appended to existing interests.
{% endapi-method-parameter %}

{% api-method-parameter name="sms\_status" type="string" required=false %}
Either "active", "stop", "less" "undeliverable", "pending", or "unknown"
{% endapi-method-parameter %}

{% api-method-parameter name="sms\_paused" type="boolean" required=false %}
Whether a user is in a support conversation.
{% endapi-method-parameter %}

{% api-method-parameter name="source" type="string" required=false %}
Immutable. Will only be set on new records.
{% endapi-method-parameter %}

{% api-method-parameter name="source\_detail" type="string" required=false %}
Only accepted alongside a valid "source".
{% endapi-method-parameter %}

{% api-method-parameter name="created\_at" type="number" required=false %}
Timestamp.
{% endapi-method-parameter %}

{% api-method-parameter name="race" type="string" required=false %}

{% endapi-method-parameter %}

{% api-method-parameter name="religion" type="string" required=false %}

{% endapi-method-parameter %}

{% api-method-parameter name="college\_name" type="string" required=false %}

{% endapi-method-parameter %}

{% api-method-parameter name="college\_name" type="string" required=false %}

{% endapi-method-parameter %}

{% api-method-parameter name="degree\_type" type="string" required=false %}

{% endapi-method-parameter %}

{% api-method-parameter name="major\_name" type="string" required=false %}

{% endapi-method-parameter %}

{% api-method-parameter name="hs\_gradyear" type="string" required=false %}

{% endapi-method-parameter %}

{% api-method-parameter name="hs\_name" type="string" required=false %}

{% endapi-method-parameter %}

{% api-method-parameter name="sat\_math" type="number" required=false %}

{% endapi-method-parameter %}

{% api-method-parameter name="sat\_verbal" type="number" required=false %}

{% endapi-method-parameter %}

{% api-method-parameter name="sat\_writing" type="number" required=false %}

{% endapi-method-parameter %}
{% endapi-method-body-parameters %}
{% endapi-method-request %}

{% api-method-response %}
{% api-method-response-example httpCode=200 %}
{% api-method-response-example-description %}

{% endapi-method-response-example-description %}

```javascript
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
{% endapi-method-response-example %}
{% endapi-method-response %}
{% endapi-method-spec %}
{% endapi-method %}

{% api-method method="put" host="https://identity.dosomething.org" path="/v1/users/:term/:id" %}
{% api-method-summary %}
Update a User
{% endapi-method-summary %}

{% api-method-description %}
Update a user.
{% endapi-method-description %}

{% api-method-spec %}
{% api-method-request %}
{% api-method-path-parameters %}
{% api-method-parameter name="term" type="string" required=false %}
Either "\_id" to specify a Northstar ID or "drupal\_id" to specify a Drupal ID.
{% endapi-method-parameter %}

{% api-method-parameter name="id" type="string" required=false %}

{% endapi-method-parameter %}
{% endapi-method-path-parameters %}
{% endapi-method-request %}

{% api-method-response %}
{% api-method-response-example httpCode=200 %}
{% api-method-response-example-description %}

{% endapi-method-response-example-description %}

```javascript
{
    "data": {
        "id": "5430e850dt8hbc541c37tt3d",
        "first_name": "New First Name",
        // the rest of the profile...
    }
}
```
{% endapi-method-response-example %}
{% endapi-method-response %}
{% endapi-method-spec %}
{% endapi-method %}

{% api-method method="delete" host="https://identity.dosomething.org" path="/v1/users/:user\_id" %}
{% api-method-summary %}
Delete a User
{% endapi-method-summary %}

{% api-method-description %}
Delete a user.
{% endapi-method-description %}

{% api-method-spec %}
{% api-method-request %}
{% api-method-path-parameters %}
{% api-method-parameter name="user\_id" type="string" required=false %}
Northstar ID for the user to delete.
{% endapi-method-parameter %}
{% endapi-method-path-parameters %}
{% endapi-method-request %}

{% api-method-response %}
{% api-method-response-example httpCode=200 %}
{% api-method-response-example-description %}

{% endapi-method-response-example-description %}

```javascript
{
    "success": {
        "code": 200,
        "message": "No Content."
    }
}
```
{% endapi-method-response-example %}
{% endapi-method-response %}
{% endapi-method-spec %}
{% endapi-method %}



