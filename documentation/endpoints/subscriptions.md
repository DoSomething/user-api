# Subscriptions Endpoint

This endpoint has no authentication, but is rate limited to 10 requests per hour per IP.

## Add a subscription topic to a user by email

Tries to find a user by email, and creates a new user if one is not found.

If the user already exists, the given `email_subscription_topic` is added to the user.

If a new user was created, the given `email_subscription_topic`, `source`, and `source_detail` are set on the user. _A new user is also sent an activate account email, corresponding to the newsletter that they have just signed up for._

```
POST /v2/subscriptions
```

**Request Parameters:**

```js
{
  /* The email of the user to find or create. */
  email: String;

  /* The email subscription topic to add to the user.
   *
   * Valid topics:
   * - 'lifestyle'
   * - `news`
   * - 'scholarships'
   * - `community`
   */
  email_subscription_topic: String;

  /* These fields will only be set on new users. */
  source: String;
  source_detail: String;
}
```

<details>
<summary><strong>Example Request</strong></summary>

```sh
  curl -X "POST" "http://northstar.test/v2/subscriptions" \
     -H 'Accept: application/json' \
     -H 'Content-Type: application/json; charset=utf-8' \
     -d $'{
  "email": "funner@dosomething.org",
  "source_details": "subscription-page",
  "email_subscription_topic": "lifestyle",
  "source": "phoenix-next"
}'
```

</details>

<details>
<summary><strong>Example Response</strong></summary>

```js
// 200 OK

{
  "data": {
    "id": "5dc5fd76fdce2717885506a2",
    "display_name": null,
    "first_name": null,
    "last_initial": "",
    "photo": null,
    "voting_plan_method_of_transport": null,
    "voting_plan_time_of_day": null,
    "voting_plan_attending_with": null,
    "language": null,
    "country": null,
    "sms_status": null,
    "sms_paused": false,
    "email_subscription_topics": [
      "lifestyle"
    ],
    "role": "user",
    "updated_at": "2019-11-12T00:11:20+00:00",
    "created_at": "2019-11-08T23:42:46+00:00"
  }
}
```

</details>
