# Reset Endpoints

The `write` scope is required for the create endpoint.

## Send a Password Reset Email

Generates a valid password reset URL for the provided user ID, and sends it to user by posting a [`call_to_action_email` event to Customer.io](http://docs.dosomething.org/customer-io#call-to-action-email) with the provided reset `type`. This requires admin privileges.

```
POST /v2/resets
```

**Request Parameters:**

```js
{
  /* The user id to send a password reset email to. */
  id: String

  /* The type of password reset email to send.
   * Valid types:
   * - 'forgot-password'
   * - 'rock-the-vote-activate-account' 
   */
  type: String
}
```

<details>
<summary><strong>Example Request</strong></summary>

```sh
curl -X POST \
  -H "Authorization: Bearer ${ACCESS_TOKEN}" \
  -H "Content-Type: application/json" -H "Accept: application/json" \
  -d "{\"id\" : \"5846c3949a8920472d4c8793\", \"type\" : \"forgot-password\"}"
  https://northstar.dosomething.org/v2/resets
```
</details>

<details>
<summary><strong>Example Response</strong></summary>

```js
// 200 OK

{
    "success": {
        "code": 200,
        "message": "Message sent."
    }
}
```
</details>

