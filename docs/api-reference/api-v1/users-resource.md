# Users Resource

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



