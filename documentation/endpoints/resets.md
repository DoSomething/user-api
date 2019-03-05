# Reset Endpoints
The `write` scope is required for the create endpoint.

## Send a Password Reset Email

Sends a password reset email to the user with provided user ID. This requires admin privileges.

```
POST /v2/resets
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

