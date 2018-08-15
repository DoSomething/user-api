# Profile Resource

{% api-method method="get" host="https://identity.dosomething.org" path="/v1/profile" %}
{% api-method-summary %}
Retrieve Authenticated User
{% endapi-method-summary %}

{% api-method-description %}
Get profile data for the currently authenticated user.
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
{% endapi-method-request %}

{% api-method-response %}
{% api-method-response-example httpCode=200 %}
{% api-method-response-example-description %}

{% endapi-method-response-example-description %}

```javascript
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
{% endapi-method-response-example %}
{% endapi-method-response %}
{% endapi-method-spec %}
{% endapi-method %}

{% api-method method="patch" host="" path="" %}
{% api-method-summary %}
Update Authenticated User
{% endapi-method-summary %}

{% api-method-description %}
Update profile data for the currently authenticated user.
{% endapi-method-description %}

{% api-method-spec %}
{% api-method-request %}
{% api-method-headers %}
{% api-method-parameter name="Authorization" type="string" required=false %}
Bearer ${ACCESS\_TOKEN}
{% endapi-method-parameter %}

{% api-method-parameter name="Content-Type" type="string" required=false %}
application/json
{% endapi-method-parameter %}

{% api-method-parameter name="Accept" type="string" required=false %}
application/json
{% endapi-method-parameter %}
{% endapi-method-headers %}

{% api-method-body-parameters %}
{% api-method-parameter name="" type="string" required=false %}
@TODO: Copy params over from User Update method!
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
The `write` scope is required for the update endpoint.
{% endhint %}

