# Reset Endpoints

The `write` scope is required for the create endpoint.

## Send a Password Reset Email

Generates a valid password reset URL for the provided user ID, and emails it to user via [Customer.io](http://docs.dosomething.org/customer-io#call-to-action-email). This requires admin privileges.

```
POST /v2/resets
```

**Request Parameters:**

```js
{
  /* The user id to send a password reset email to. */
  id: String

  /* The type of password reset email to send.
   *
   * Valid types:
   * - 'forgot-password'
   * - 'breakdown-activate-account'
   * - 'rock-the-vote-activate-account' 
   */
  type: String
}
```
Looking to send a new type of password reset email? Check out http://docs.dosomething.org/non-traditional-member-activation#create-new-type.

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
