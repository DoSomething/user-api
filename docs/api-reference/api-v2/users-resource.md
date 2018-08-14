# Users Resource

{% api-method method="get" host="https://identity.dosomething.org" path="/v2/users" %}
{% api-method-summary %}
Retrieve all Users
{% endapi-method-summary %}

{% api-method-description %}
Get an index list of all users in a paginated format.
{% endapi-method-description %}

{% api-method-spec %}
{% api-method-request %}
{% api-method-headers %}
{% api-method-parameter name="Authorization" type="string" required=true %}
Bearer ${ACCESS\_TOKEN}
{% endapi-method-parameter %}

{% api-method-parameter name="Content-Type" type="string" required=true %}
application/json
{% endapi-method-parameter %}

{% api-method-parameter name="Accept" type="string" required=true %}
application/json
{% endapi-method-parameter %}
{% endapi-method-headers %}

{% api-method-query-parameters %}
{% api-method-parameter name="pagination" type="string" required=false %}
Either "standard" or "cursor". Cursor pagination is significantly faster, but does not provide any information on the total number of results \(only whether another page exists\).
{% endapi-method-parameter %}

{% api-method-parameter name="limit" type="number" required=false %}
Set the number of results to include per page. Default is 20. Maximum is 100.
{% endapi-method-parameter %}

{% api-method-parameter name="page" type="string" required=false %}
Set the page number to get results from.
{% endapi-method-parameter %}

{% api-method-parameter name="before" type="string" required=false %}
Filter the collection to include only users with timestamps before the given date or datetime.
{% endapi-method-parameter %}

{% api-method-parameter name="after" type="string" required=false %}
Filter the collection to include only users with timestamps after the given date or datetime.
{% endapi-method-parameter %}

{% api-method-parameter name="filter" type="string" required=false %}
Filter the collection to include only users matching the following comma-separated values.
{% endapi-method-parameter %}

{% api-method-parameter name="search" type="string" required=false %}
Search the collection for users with fields whose value match the query.
{% endapi-method-parameter %}
{% endapi-method-query-parameters %}
{% endapi-method-request %}

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
                "next": "https://northstar.dosomething.org/v2/users?page=2",
            }
        ]
    }
}
```
{% endapi-method-response-example %}
{% endapi-method-response %}
{% endapi-method-spec %}
{% endapi-method %}

{% hint style="info" %}
This requires the `user` scope and either the `admin` scope, or "admin" or "staff" role with the appropriate scope. The `write` scope is required for create/update/delete endpoints.
{% endhint %}

### Additional Notes

If using the `filter` query parameter, you could make a request like:

```text
/v2/users?filter[drupal_id]=10123,10124,10125
```

Which would return users whose Drupal ID is either `10123`, `10124`, or `10125`. You can also filter by one or more indexed fields.

With the `search` query parameter, you can search by id, drupal\_id, email, and mobile. For example, you can search with or without specifying an indexed column:

```text
/v2/users?search[email]=test@example.org
```

or

```text
v2/users?search=clee@dosomething.org
```

Both ways are now supported.

{% api-method method="post" host="https://identity.dosomething.org" path="/v2/users" %}
{% api-method-summary %}
Create a User
{% endapi-method-summary %}

{% api-method-description %}
Create a new user.
{% endapi-method-description %}

{% api-method-spec %}
{% api-method-request %}
{% api-method-headers %}
{% api-method-parameter name="Authorization" type="string" required=true %}
Bearer ${ACCESS\_TOKEN}
{% endapi-method-parameter %}

{% api-method-parameter name="Content-Type" type="string" required=true %}
application/json
{% endapi-method-parameter %}

{% api-method-parameter name="Accept" type="string" required=true %}
application/json
{% endapi-method-parameter %}
{% endapi-method-headers %}

{% api-method-query-parameters %}
{% api-method-parameter name="upsert" type="boolean" required=false %}
Should this request upsert an existing account, if matched? Defaults to "true".
{% endapi-method-parameter %}
{% endapi-method-query-parameters %}

{% api-method-body-parameters %}
{% api-method-parameter name="email" type="string" required=true %}
Required if "mobile" or "facebook\_id" is not provided.
{% endapi-method-parameter %}

{% api-method-parameter name="mobile" type="string" required=true %}
Required if "email" or "facebook\_id" is not provided.
{% endapi-method-parameter %}

{% api-method-parameter name="facebook\_id" type="number" required=true %}
Required if "email" or "mobile" is not provided.
{% endapi-method-parameter %}

{% api-method-parameter name="password" type="string" required=false %}
Optional, but required for user to be able to log in!
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

{% api-method-parameter name="slack\_id" type="string" required=false %}

{% endapi-method-parameter %}

{% api-method-parameter name="interests" type="string" required=false %}
String or Array. CSV values or array will be appended to existing interests.
{% endapi-method-parameter %}

{% api-method-parameter name="sms\_status" type="string" required=false %}
Value of "active", "stop", "less", "undeliverable", "pending", or "unknown".
{% endapi-method-parameter %}

{% api-method-parameter name="sms\_paused" type="boolean" required=false %}
Whether a user is in a support conversation.
{% endapi-method-parameter %}

{% api-method-parameter name="source" type="string" required=false %}
Immutable. Will only be set on new records.
{% endapi-method-parameter %}

{% api-method-parameter name="source\_detail" type="string" required=false %}

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

{% hint style="info" %}
This requires either the `admin` scope, or "admin" or "staff" role with the appropriate scope.
{% endhint %}

{% hint style="info" %}
If a user already exists, an exception error with a message of`A record matching one of the given indexes already exists.` will be returned and will not create a duplicate account.
{% endhint %}

### Additional Notes

An "[upsert](https://docs.mongodb.org/v2.6/reference/glossary/#term-upsert)" will only occur if using the `upsert=true` param. If this is present and a user with a matching identifier is found, new/changed properties will be merged into the existing document. This means making the same request multiple times will _not_ create duplicate accounts.

Index fields \(such as `email`, `mobile`, `drupal_id`\) can _only_ be "upserted" if they are not already saved on the user's account. To change an existing value for one of these fields, you must explicitly update that user via the [update](https://github.com/DoSomething/northstar/blob/master/documentation/endpoints/v2/users.md#update-a-user) endpoint.

{% api-method method="get" host="https://identity.dosomething.org" path="/v2/users/:user\_id" %}
{% api-method-summary %}
Retrieve a single User
{% endapi-method-summary %}

{% api-method-description %}
Get a single user using a specified Northstar ID.
{% endapi-method-description %}

{% api-method-spec %}
{% api-method-request %}
{% api-method-path-parameters %}
{% api-method-parameter name="user\_id" type="string" required=true %}
Northstar ID. e.g. 5430e850dt8hbc541c37tt3d
{% endapi-method-parameter %}
{% endapi-method-path-parameters %}

{% api-method-headers %}
{% api-method-parameter name="Authorization" type="string" required=true %}
Bearer ${ACCESS\_TOKEN}
{% endapi-method-parameter %}

{% api-method-parameter name="Content-Type" type="string" required=true %}
application/json
{% endapi-method-parameter %}

{% api-method-parameter name="Accept" type="string" required=true %}
application/json
{% endapi-method-parameter %}
{% endapi-method-headers %}
{% endapi-method-request %}

{% api-method-response %}
{% api-method-response-example httpCode=200 %}
{% api-method-response-example-description %}

{% endapi-method-response-example-description %}

```javascript
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
{% endapi-method-response-example %}
{% endapi-method-response %}
{% endapi-method-spec %}
{% endapi-method %}

{% hint style="info" %}
Fetching a user requires either the `admin` scope, or an "admin" or "staff" role with the appropriate scope.
{% endhint %}

{% api-method method="get" host="https://identity.dosomething.org" path="/v2/email/:email" %}
{% api-method-summary %}
Retrieve a single User by Email
{% endapi-method-summary %}

{% api-method-description %}

{% endapi-method-description %}

{% api-method-spec %}
{% api-method-request %}
{% api-method-path-parameters %}
{% api-method-parameter name="email" type="string" required=true %}
Email address. e.g. test@example.com
{% endapi-method-parameter %}
{% endapi-method-path-parameters %}

{% api-method-headers %}
{% api-method-parameter name="Authorization" type="string" required=true %}
Bearer ${ACCESS\_TOKEN}
{% endapi-method-parameter %}

{% api-method-parameter name="Content-Type" type="string" required=true %}
application/json
{% endapi-method-parameter %}

{% api-method-parameter name="Accept" type="string" required=true %}
application/json
{% endapi-method-parameter %}
{% endapi-method-headers %}
{% endapi-method-request %}

{% api-method-response %}
{% api-method-response-example httpCode=200 %}
{% api-method-response-example-description %}

{% endapi-method-response-example-description %}

```javascript
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
{% endapi-method-response-example %}
{% endapi-method-response %}
{% endapi-method-spec %}
{% endapi-method %}

{% hint style="info" %}
Fetching a user requires either the `admin` scope, or an "admin" or "staff" role with the appropriate scope.
{% endhint %}

{% api-method method="get" host="https://identity.dosomething.org" path="/v2/mobile/:mobile" %}
{% api-method-summary %}
Retrieve a single User by Mobile
{% endapi-method-summary %}

{% api-method-description %}
Get a single user using a specified mobile number.
{% endapi-method-description %}

{% api-method-spec %}
{% api-method-request %}
{% api-method-path-parameters %}
{% api-method-parameter name="mobile" type="string" required=true %}
Mobile phone number. e.g. 5555555555
{% endapi-method-parameter %}
{% endapi-method-path-parameters %}

{% api-method-headers %}
{% api-method-parameter name="Authorization" type="string" required=true %}
Bearer ${ACCESS\_TOKEN}
{% endapi-method-parameter %}

{% api-method-parameter name="Content-Type" type="string" required=true %}
application/json
{% endapi-method-parameter %}

{% api-method-parameter name="Accept" type="string" required=true %}
application/json
{% endapi-method-parameter %}
{% endapi-method-headers %}
{% endapi-method-request %}

{% api-method-response %}
{% api-method-response-example httpCode=200 %}
{% api-method-response-example-description %}

{% endapi-method-response-example-description %}

```javascript
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
{% endapi-method-response-example %}
{% endapi-method-response %}
{% endapi-method-spec %}
{% endapi-method %}

{% hint style="info" %}
Fetching a user requires either the `admin` scope, or an "admin" or "staff" role with the appropriate scope.
{% endhint %}

{% api-method method="patch" host="https://identity.dosomething.org" path="/v2/users/:user\_id" %}
{% api-method-summary %}
Update a single User
{% endapi-method-summary %}

{% api-method-description %}
Update a single user, retrieved with the user's specified Northstar ID.
{% endapi-method-description %}

{% api-method-spec %}
{% api-method-request %}
{% api-method-path-parameters %}
{% api-method-parameter name="user\_id" type="string" required=true %}
Northstar ID. e.g. 5430e850dt8hbc541c37tt3d
{% endapi-method-parameter %}
{% endapi-method-path-parameters %}

{% api-method-headers %}
{% api-method-parameter name="Authorization" type="string" required=true %}
Bearer ${ACCESS\_TOKEN}
{% endapi-method-parameter %}

{% api-method-parameter name="Content-Type" type="string" required=true %}
application/json
{% endapi-method-parameter %}

{% api-method-parameter name="Accept" type="string" required=true %}
application/json
{% endapi-method-parameter %}
{% endapi-method-headers %}

{% api-method-body-parameters %}
{% api-method-parameter name="email" type="string" required=false %}

{% endapi-method-parameter %}

{% api-method-parameter name="mobile" type="string" required=false %}

{% endapi-method-parameter %}

{% api-method-parameter name="facebook\_id" type="number" required=false %}

{% endapi-method-parameter %}

{% api-method-parameter name="password" type="string" required=false %}

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

{% api-method-parameter name="skack\_id" type="string" required=false %}

{% endapi-method-parameter %}

{% api-method-parameter name="interests" type="string" required=false %}
String or Array. CSV values or array will be appended to existing interests.
{% endapi-method-parameter %}

{% api-method-parameter name="role" type="string" required=false %}
Can only be modified by admin. Value of "user" \(default\), "staff", or "admin".
{% endapi-method-parameter %}

{% api-method-parameter name="sms\_status" type="string" required=false %}
Value of "active", "stop", "less", "undeliverable", "pending", or "unknown".
{% endapi-method-parameter %}

{% api-method-parameter name="sms\_paused" type="boolean" required=false %}
Whether a user is in a support conversation.
{% endapi-method-parameter %}

{% api-method-parameter name="race" type="string" required=false %}

{% endapi-method-parameter %}

{% api-method-parameter name="religion" type="string" required=false %}

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

{% hint style="info" %}
This requires either the `admin` scope, or "admin" or "staff" role with the appropriate scope.
{% endhint %}

{% api-method method="delete" host="https://identity.dosomething.org" path="/v2/users/:user\_id" %}
{% api-method-summary %}
Destroy a single User
{% endapi-method-summary %}

{% api-method-description %}
Destroy a specified user resource.
{% endapi-method-description %}

{% api-method-spec %}
{% api-method-request %}
{% api-method-path-parameters %}
{% api-method-parameter name="user\_id" type="string" required=true %}
Northstar ID. e.g. 5430e850dt8hbc541c37tt3d
{% endapi-method-parameter %}
{% endapi-method-path-parameters %}

{% api-method-headers %}
{% api-method-parameter name="Authorization" type="string" required=true %}
Bearer ${ACCESS\_TOKEN}
{% endapi-method-parameter %}

{% api-method-parameter name="Content-Type" type="string" required=true %}
application/json
{% endapi-method-parameter %}

{% api-method-parameter name="Accept" type="string" required=true %}
application/json
{% endapi-method-parameter %}
{% endapi-method-headers %}
{% endapi-method-request %}

{% api-method-response %}
{% api-method-response-example httpCode=200 %}
{% api-method-response-example-description %}

{% endapi-method-response-example-description %}

```
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

