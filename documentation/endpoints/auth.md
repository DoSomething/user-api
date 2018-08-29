# Authentication Endpoints
Northstar acts as the authorization server for all of our internal services at DoSomething.org. Individual services may
send a user's credentials to Northstar in exchange for a signed access token which can be used throughout our ecosystem.

Access tokens are digitally signed [JSON Web Tokens](http://jwt.io), which can then be passed between other services
and verified _without_ requiring each service to continually ping Northstar for each request. Because access tokens have
a short lifetime, the token can be "logged out" of all services by revoking their refresh token.

Each access token includes the authorized user's ID, expiration timestamp, and scopes. Tokens are signed to prevent
tampering, and can be verified using a shared public key.

__Here's the tl;dr:__ If a user is logging in to an application and making requests, use the [Authorization Code grant](#create-token-authorization-code-grant)
 to request an access & refresh token for them. If you're performing requests as a "machine" (not as a direct result of a
 user's action), use the [Client Credentials Grant](#create-token-client-credentials-grant).

## Create Token (Authorization Code Grant)
The authorization code grant allows you to authorize a user without needing to manually handle their username or password.
It's a two-step process that involves redirecting the user to Northstar in their web browser, and then using the "code"
returned to the application's redirect URL to request an access & refresh token.

#### Step One: Authorize the User

Redirect the user to Northstar's "authorize" page with the following query string parameters:

* `response_type` with the value `code`
* `client_id` with your Client ID
* `scope` with a space-delimited list of scopes to request
* `state` with a CSRF token that can be validated below
* `redirect_uri` with the address to redirect to
* `destination` with a destination to display on the login page (optional)
* `title` with a title to display on the registration page (optional)
* `callToAction` with a call to action to display on the registration page (optional)
* `coverImage` with a link to a cover image to display on the registration page (optional)


For example, an application named `puppet-sloth` may initiate a user authorization request like so:

```
GET /authorize?response_type=code&client_id=puppet-sloth&scope=user&state=MCceWSE5vHVyYQovh3CL4UWBqe0Uhcpf&redirect_uri=https%3A%2F%2Fidentity-qa.dosomething.org%2Fcallback
```

The user will be presented with a login page (unless they've previously logged in to Northstar, in which case we'll just use
their existing session), and then redirected back to the given `redirect_uri` with the following values
in the query string of the request:

* `code` with the authorization code (used below)
* `state` with the CSRF token (compare this to what you provided!)

#### Step Two: Request a Token

You may now use the provided code to request a token:

```
POST /v2/auth/token
```

```js
// Content-Type: application/json

{
  grant_type: 'authorization_code',

  // The client application's Client ID (required)
  client_id: String,

  // The client application's Client Secret (required)
  client_secret: String,

  // The authorization code returned in Step One (required)
  code: String
}
```

<details>
<summary><strong>Example Request</strong></summary>

```sh
curl -X POST \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"grant_type": "authorization_code", "client_id": "${CLIENT_ID}", "client_secret": "${CLIENT_SECRET}", \
  "code": "KwM/cj40QWuCmpEALcmjxEOeXmcvoYNBQCb7pWd6X0yEG4fRn/b58C8oEos4SRUhSAjOgoZMcKk+rdk9hbd9u5rvFoC3pj8oIFTMyig1fFE0Lpvvu"}' \
  https://northstar.dosomething.org/v2/auth/token
```
</details>


<details>
<summary><strong>Example Response</strong></summary>

```js
// 200 OK

{
  "token_type": "Bearer",
  "expires_in": 3600,
  "access_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsImp0aSI6IjU1ZDEwNjk4MTAzNDliMDVhNjdjODI1NDQ2NzIxYmFhNTcyMDM2MTg1MDNhOTlhYjA5ZWYxODVmMGJhZTI2MmJhY2VjZWVhMTY2YjIwYmE5In0.eyJhdWQiOiIiLCJqdGkiOiI1NWQxMDY5ODEwMzQ5YjA1YTY3YzgyNTQ0NjcyMWJhYTU3MjAzNjE4NTAzYTk5YWIwOWVmMTg1ZjBiYWUyNjJiYWNlY2VlYTE2NmIyMGJhOSIsImlhdCI6MTQ2MDQwMDU0NCwibmJmIjoxNDYwNDAwNTQ0LCJleHAiOjE0NjA0MDQxNDQsInN1YiI6IjU0MzBlODUwZHQ4aGJjNTQxYzM3dHQzZCIsInNjb3BlcyI6WyJhZG1pbiJdfQ.Q9SvBEjbJlDEBbBzzvxiL_Dg_nC29Zz34Slrs5WdDdxPKrIwHI6SqnjPvMo4gwoWTr2s9dWye--3Dv0hNNn3xIo7MF6b6DDS96XKplzFRGx2043AzPIVmxxDPz4QdeF18Lnx5W2Aj-_YdRGc-S2n-du2rVYTaGpEzVII4W4Wh7Q",
  "refresh_token": "EytNzc1CJrA0fn1ymUutcg8FzOM7yUER5F+31oP/eRJdXwwaII6Lw4yS/PrC/orThdot4+7o81d/VXdUDBre6NDsMbEtTjk9fJVPDFSU74focg3N0zXKiPziBRvegv4DLrM2RkAfYYfxTK5nM1uMT2pCNBobrA8qHahgmw2XgoSE4J/xco/lmHKP393KMwn0nziKDr0YeqPRi+PAvtdsNPKpydyc0JbAFEevZ2UYXz4bRIaS4nUP+IyB6cYSdnok3OCJr8lDUp/OHA0JlOk9ra7YBFXNB8ZvlR1GEL2qQBlIWCqxPL9xrUBTIWUst7/+imx8LmBqevmGY1UFBXAm7n0p1Ih3Qxj0dx9u5woBdCwLYxAlEL70LaSDbx3qdhF+6uhrZTCnpOPE/tZSImpbmashh/SLtFEMpVP+ifISnLYSnQTvyL4XvWU/8azrFGmDmxYB63kuR4D+4QcqptPyA8JC5sOnn1CpDwzTcn93WMbhtWdIUCBTgF2R8rYNVki5"
}
```
</details>

## Create Token (Refresh Token Grant)

This grant should be used when the access token given by the [Authorization Code grant](#create-token-authorization-code-grant) expires. It will verify the provided refresh token (given alongside the original access token) and create a new JWT authentication token. The provided refresh token will be "consumed" and a new refresh token will be returned.

If an invalid refresh token is provided, a `400 Bad Request` error will be returned.

```
POST /v2/auth/token
```

**Request Parameters:**

```js
// Content-Type: application/json

{
  grant_type: 'refresh_token',

  // The client application's Client ID (required)
  client_id: String,

  // The client application's Client Secret (required for "trusted" applications)
  client_secret: String,

  /* An unused refresh token, returned from the Password Grant */
  refresh_token: String,

  /* Optional: Adjust the scopes for the new access token. */
  scope: String,
}
```

<details>
<summary><strong>Example Request</strong></summary>

```sh
curl -X POST \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"grant_type": "refresh_token", "client_id": "${CLIENT_ID}", "client_secret": "${CLIENT_SECRET}", \
  "refresh_token": "${REFRESH_TOKEN}"}' \
  https://northstar.dosomething.org/v2/auth/token
```

</details>

<details>
<summary><strong>Example Response</strong></summary>

```js
// 200 OK

{
  "token_type": "Bearer",
  "expires_in": 3600,
  "access_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsImp0aSI6IjU1ZDEwNjk4MTAzNDliMDVhNjdjODI1NDQ2NzIxYmFhNTcyMDM2MTg1MDNhOTlhYjA5ZWYxODVmMGJhZTI2MmJhY2VjZWVhMTY2YjIwYmE5In0.eyJhdWQiOiIiLCJqdGkiOiI1NWQxMDY5ODEwMzQ5YjA1YTY3YzgyNTQ0NjcyMWJhYTU3MjAzNjE4NTAzYTk5YWIwOWVmMTg1ZjBiYWUyNjJiYWNlY2VlYTE2NmIyMGJhOSIsImlhdCI6MTQ2MDQwMDU0NCwibmJmIjoxNDYwNDAwNTQ0LCJleHAiOjE0NjA0MDQxNDQsInN1YiI6IjU0MzBlODUwZHQ4aGJjNTQxYzM3dHQzZCIsInNjb3BlcyI6WyJhZG1pbiJdfQ.Q9SvBEjbJlDEBbBzzvxiL_Dg_nC29Zz34Slrs5WdDdxPKrIwHI6SqnjPvMo4gwoWTr2s9dWye--3Dv0hNNn3xIo7MF6b6DDS96XKplzFRGx2043AzPIVmxxDPz4QdeF18Lnx5W2Aj-_YdRGc-S2n-du2rVYTaGpEzVII4W4Wh7Q",
  "refresh_token": "EytNzc1CJrA0fn1ymUutcg8FzOM7yUER5F+31oP/eRJdXwwaII6Lw4yS/PrC/orThdot4+7o81d/VXdUDBre6NDsMbEtTjk9fJVPDFSU74focg3N0zXKiPziBRvegv4DLrM2RkAfYYfxTK5nM1uMT2pCNBobrA8qHahgmw2XgoSE4J/xco/lmHKP393KMwn0nziKDr0YeqPRi+PAvtdsNPKpydyc0JbAFEevZ2UYXz4bRIaS4nUP+IyB6cYSdnok3OCJr8lDUp/OHA0JlOk9ra7YBFXNB8ZvlR1GEL2qQBlIWCqxPL9xrUBTIWUst7/+imx8LmBqevmGY1UFBXAm7n0p1Ih3Qxj0dx9u5woBdCwLYxAlEL70LaSDbx3qdhF+6uhrZTCnpOPE/tZSImpbmashh/SLtFEMpVP+ifISnLYSnQTvyL4XvWU/8azrFGmDmxYB63kuR4D+4QcqptPyA8JC5sOnn1CpDwzTcn93WMbhtWdIUCBTgF2R8rYNVki5"
}
```
</details>

## Create Token (Client Credentials Grant)
This will verify a client application's credentials and create a JWT authentication token, which can be used to sign future
requests by the application. If invalid credentials are provided, this endpoint will return a `401 Unauthorized` error.

```
POST /v2/auth/token
```

**Request Parameters:**

```js
// Content-Type: application/json

{
  grant_type: 'client_credentials',

  // The client application's Client ID (required)
  client_id: String,

  // The client application's Client Secret (required for "trusted" applications)
  client_secret: String,

  /* Scopes to request, space-delimited. */
  scope: String,
}
```

<details>
<summary><strong>Example Request</strong></summary>

```sh
curl -X POST \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"grant_type": "client_credentials", "client_id": "${CLIENT_ID}", "client_secret": "${CLIENT_SECRET}"'
  https://northstar.dosomething.org/v2/auth/token
```
</details>

<details>
<summary><strong>Example Response</strong></summary>

```js
// 200 OK

{
  "token_type": "Bearer",
  "expires_in": 3600,
  "access_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsImp0aSI6IjU1ZDEwNjk4MTAzNDliMDVhNjdjODI1NDQ2NzIxYmFhNTcyMDM2MTg1MDNhOTlhYjA5ZWYxODVmMGJhZTI2MmJhY2VjZWVhMTY2YjIwYmE5In0.eyJhdWQiOiIiLCJqdGkiOiI1NWQxMDY5ODEwMzQ5YjA1YTY3YzgyNTQ0NjcyMWJhYTU3MjAzNjE4NTAzYTk5YWIwOWVmMTg1ZjBiYWUyNjJiYWNlY2VlYTE2NmIyMGJhOSIsImlhdCI6MTQ2MDQwMDU0NCwibmJmIjoxNDYwNDAwNTQ0LCJleHAiOjE0NjA0MDQxNDQsInN1YiI6IjU0MzBlODUwZHQ4aGJjNTQxYzM3dHQzZCIsInNjb3BlcyI6WyJhZG1pbiJdfQ.Q9SvBEjbJlDEBbBzzvxiL_Dg_nC29Zz34Slrs5WdDdxPKrIwHI6SqnjPvMo4gwoWTr2s9dWye--3Dv0hNNn3xIo7MF6b6DDS96XKplzFRGx2043AzPIVmxxDPz4QdeF18Lnx5W2Aj-_YdRGc-S2n-du2rVYTaGpEzVII4W4Wh7Q"
}
```

</details>

## Revoke Token

This will revoke the provided refresh token, if the user is authorized to do so.

```
DELETE /v2/auth/token
```

**Request Parameters:**

```js
// Content-Type: application/json

{
  token: String // The refresh token to be revoked.
}
```

<details>
<summary><strong>Example Request</strong></summary>

```sh
curl -X DELETE \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"token": "${REFRESH_TOKEN}"}'
  https://northstar.dosomething.org/v2/auth/token
```

</details>

<details>
<summary><strong>Example Response</strong></summary>

```js
// 200 OK

{
  "success": {
    "code": 200,
    "message": "That refresh token has been successfully revoked."
  }
}
```

</details>

## Get User Info

This will display the user's profile according to the format [defined in the OpenID Connect specification](http://openid.net/specs/openid-connect-core-1_0.html#UserInfo).

```
GET /v2/auth/info
```

<details>
<summary><strong>Example Request</strong></summary>

```sh
curl -X GET \
  -H "Authorization: Bearer ${ACCESS_TOKEN}" \
  -H "Accept: application/json" \
  https://northstar.dosomething.org/v2/auth/info
```
</details>

<details>
<summary><strong>Example Response</strong></summary>

```js
// 200 OK

{
  "data": {
    "id": "57dac28fbffebcb7708b4567",
    "given_name": "Ezra",
    "family_name": null,
    "email": "test@dosomething.org",
    "phone_number": "3294927429",
    "birthdate": "1990-10-25",
    "address": {
      "street_address": "518 Lorenza Creek Suite 862\n",
      "locality": null,
      "region": "AL",
      "postal_code": "04296",
      "country": "SY"
    },
    "updated_at": 1473954542,
    "created_at": 1473954447
  }
}
```
</details>
