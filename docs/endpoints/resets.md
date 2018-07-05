# Reset Endpoints
The `write` scope is required for the create endpoint.

## Create a Password Reset Link
Create a new password reset link for the provided user ID. This requires admin privileges.

```
POST /v2/resets
```

<details>
<summary><strong>Example Request</strong></summary>

```sh
curl -X POST \
  -H "Authorization: Bearer ${ACCESS_TOKEN}" \
  -H "Content-Type: application/json" -H "Accept: application/json" \
  -d "{\"id\" : \"5846c3949a8920472d4c8793\"}"
  https://northstar.dosomething.org/v2/resets
```
</details>

<details>
<summary><strong>Example Response</strong></summary>

```js
// 200 OK

{
  "url": "http:\/\/northstar.dev:8000\/password\/reset\/5d8c35cb8d5151ec2fa8b278fd17e0ba19f1a52a3c01ffc9c2e454961038fb1d?email=passwordless-fool92%40dosomething.org"
}
```
</details>

