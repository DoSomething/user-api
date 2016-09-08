# Authentication Endpoints

Northstar acts as the authorization server for all of our internal services at DoSomething.org. Individual services may
send a user's credentials to Northstar in exchange for a signed access token which can be used throughout our ecosystem.

Access tokens are digitally signed [JSON Web Tokens](http://jwt.io), which can then be passed between other services
and verified _without_ requiring each service to continually ping Northstar for each request. Because access tokens have
a short lifetime, a user can be "logged out" of all services by revoking their refresh token.

Each access token includes the authorized user's ID, expiration timestamp, and scopes. Tokens are signed to prevent
tampering, and can be verified using a shared public key.

## Create Token (Password Grant)

This will verify a user's credentials and create a JWT authentication token, which can be used to sign future requests
on the user's behalf, and a refresh token, which can be used to fetch a new access token after the one expires.
If invalid credentials are provided, this endpoint will return a `401 Unauthorized` error.

```
POST /v2/auth/token
```

**Parameters:**

```js
// Content-Type: application/json

{
  grant_type: 'password',

  // The client application's Client ID (required)
  client_id: String,

  // The client application's Client Secret (required for "trusted" applications)
  client_secret: String,

  /* Can be either the user's 'email' or 'mobile' */
  username: String,

  /* Required */
  password: String,

  /* Scopes to request, space-delimited. */
  scope: String,
}
```

**Example Request:**

```
curl -X POST \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"grant_type": "password", "client_id": "${CLIENT_ID}", "client_secret": "${CLIENT_SECRET}", \
  "username": "test@example.com", "password": "${PASSWORD}"}' \
  https://northstar.dosomething.org/v2/auth/token
```

**Example Response:**

```js
// 200 OK

{
  "token_type": "Bearer",
  "expires_in": 3600,
  "access_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsImp0aSI6IjU1ZDEwNjk4MTAzNDliMDVhNjdjODI1NDQ2NzIxYmFhNTcyMDM2MTg1MDNhOTlhYjA5ZWYxODVmMGJhZTI2MmJhY2VjZWVhMTY2YjIwYmE5In0.eyJhdWQiOiIiLCJqdGkiOiI1NWQxMDY5ODEwMzQ5YjA1YTY3YzgyNTQ0NjcyMWJhYTU3MjAzNjE4NTAzYTk5YWIwOWVmMTg1ZjBiYWUyNjJiYWNlY2VlYTE2NmIyMGJhOSIsImlhdCI6MTQ2MDQwMDU0NCwibmJmIjoxNDYwNDAwNTQ0LCJleHAiOjE0NjA0MDQxNDQsInN1YiI6IjU0MzBlODUwZHQ4aGJjNTQxYzM3dHQzZCIsInNjb3BlcyI6WyJhZG1pbiJdfQ.Q9SvBEjbJlDEBbBzzvxiL_Dg_nC29Zz34Slrs5WdDdxPKrIwHI6SqnjPvMo4gwoWTr2s9dWye--3Dv0hNNn3xIo7MF6b6DDS96XKplzFRGx2043AzPIVmxxDPz4QdeF18Lnx5W2Aj-_YdRGc-S2n-du2rVYTaGpEzVII4W4Wh7Q",
  "refresh_token": "EytNzc1CJrA0fn1ymUutcg8FzOM7yUER5F+31oP/eRJdXwwaII6Lw4yS/PrC/orThdot4+7o81d/VXdUDBre6NDsMbEtTjk9fJVPDFSU74focg3N0zXKiPziBRvegv4DLrM2RkAfYYfxTK5nM1uMT2pCNBobrA8qHahgmw2XgoSE4J/xco/lmHKP393KMwn0nziKDr0YeqPRi+PAvtdsNPKpydyc0JbAFEevZ2UYXz4bRIaS4nUP+IyB6cYSdnok3OCJr8lDUp/OHA0JlOk9ra7YBFXNB8ZvlR1GEL2qQBlIWCqxPL9xrUBTIWUst7/+imx8LmBqevmGY1UFBXAm7n0p1Ih3Qxj0dx9u5woBdCwLYxAlEL70LaSDbx3qdhF+6uhrZTCnpOPE/tZSImpbmashh/SLtFEMpVP+ifISnLYSnQTvyL4XvWU/8azrFGmDmxYB63kuR4D+4QcqptPyA8JC5sOnn1CpDwzTcn93WMbhtWdIUCBTgF2R8rYNVki5"
}
```


## Create Token (Client Credentials Grant)

This will verify a client application's credentials and create a JWT authentication token, which can be used to sign future
requests by the application. If invalid credentials are provided, this endpoint will return a `401 Unauthorized` error.

```
POST /v2/auth/token
```

**Parameters:**

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

**Example Request:**
```
curl -X POST \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"grant_type": "client_credentials", "client_id": "${CLIENT_ID}", "client_secret": "${CLIENT_SECRET}"'
  https://northstar.dosomething.org/v2/auth/token
```

**Example Response:**

```js
// 200 OK

{
  "token_type": "Bearer",
  "expires_in": 3600,
  "access_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsImp0aSI6IjU1ZDEwNjk4MTAzNDliMDVhNjdjODI1NDQ2NzIxYmFhNTcyMDM2MTg1MDNhOTlhYjA5ZWYxODVmMGJhZTI2MmJhY2VjZWVhMTY2YjIwYmE5In0.eyJhdWQiOiIiLCJqdGkiOiI1NWQxMDY5ODEwMzQ5YjA1YTY3YzgyNTQ0NjcyMWJhYTU3MjAzNjE4NTAzYTk5YWIwOWVmMTg1ZjBiYWUyNjJiYWNlY2VlYTE2NmIyMGJhOSIsImlhdCI6MTQ2MDQwMDU0NCwibmJmIjoxNDYwNDAwNTQ0LCJleHAiOjE0NjA0MDQxNDQsInN1YiI6IjU0MzBlODUwZHQ4aGJjNTQxYzM3dHQzZCIsInNjb3BlcyI6WyJhZG1pbiJdfQ.Q9SvBEjbJlDEBbBzzvxiL_Dg_nC29Zz34Slrs5WdDdxPKrIwHI6SqnjPvMo4gwoWTr2s9dWye--3Dv0hNNn3xIo7MF6b6DDS96XKplzFRGx2043AzPIVmxxDPz4QdeF18Lnx5W2Aj-_YdRGc-S2n-du2rVYTaGpEzVII4W4Wh7Q"
}
```

## Create Token (Refresh Token Grant)

This will verify a user's refresh token (given from the [password grant](#create-token-password-grant)) and create a new JWT authentication token. The provided refresh token will be "consumed" and a new refresh token will be returned.

If an invalid refresh token is provided, a `400 Bad Request` error will be returned.

```
POST /v2/auth/token
```

**Parameters:**

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

**Example Request:**
```
curl -X POST \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"grant_type": "refresh_token", "client_id": "${CLIENT_ID}", "client_secret": "${CLIENT_SECRET}", \
  "refresh_token": "${REFRESH_TOKEN}"}' \
  https://northstar.dosomething.org/v2/auth/token
```

**Example Response:**

```js
// 200 OK

{
  "token_type": "Bearer",
  "expires_in": 3600,
  "access_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsImp0aSI6IjU1ZDEwNjk4MTAzNDliMDVhNjdjODI1NDQ2NzIxYmFhNTcyMDM2MTg1MDNhOTlhYjA5ZWYxODVmMGJhZTI2MmJhY2VjZWVhMTY2YjIwYmE5In0.eyJhdWQiOiIiLCJqdGkiOiI1NWQxMDY5ODEwMzQ5YjA1YTY3YzgyNTQ0NjcyMWJhYTU3MjAzNjE4NTAzYTk5YWIwOWVmMTg1ZjBiYWUyNjJiYWNlY2VlYTE2NmIyMGJhOSIsImlhdCI6MTQ2MDQwMDU0NCwibmJmIjoxNDYwNDAwNTQ0LCJleHAiOjE0NjA0MDQxNDQsInN1YiI6IjU0MzBlODUwZHQ4aGJjNTQxYzM3dHQzZCIsInNjb3BlcyI6WyJhZG1pbiJdfQ.Q9SvBEjbJlDEBbBzzvxiL_Dg_nC29Zz34Slrs5WdDdxPKrIwHI6SqnjPvMo4gwoWTr2s9dWye--3Dv0hNNn3xIo7MF6b6DDS96XKplzFRGx2043AzPIVmxxDPz4QdeF18Lnx5W2Aj-_YdRGc-S2n-du2rVYTaGpEzVII4W4Wh7Q",
  "refresh_token": "EytNzc1CJrA0fn1ymUutcg8FzOM7yUER5F+31oP/eRJdXwwaII6Lw4yS/PrC/orThdot4+7o81d/VXdUDBre6NDsMbEtTjk9fJVPDFSU74focg3N0zXKiPziBRvegv4DLrM2RkAfYYfxTK5nM1uMT2pCNBobrA8qHahgmw2XgoSE4J/xco/lmHKP393KMwn0nziKDr0YeqPRi+PAvtdsNPKpydyc0JbAFEevZ2UYXz4bRIaS4nUP+IyB6cYSdnok3OCJr8lDUp/OHA0JlOk9ra7YBFXNB8ZvlR1GEL2qQBlIWCqxPL9xrUBTIWUst7/+imx8LmBqevmGY1UFBXAm7n0p1Ih3Qxj0dx9u5woBdCwLYxAlEL70LaSDbx3qdhF+6uhrZTCnpOPE/tZSImpbmashh/SLtFEMpVP+ifISnLYSnQTvyL4XvWU/8azrFGmDmxYB63kuR4D+4QcqptPyA8JC5sOnn1CpDwzTcn93WMbhtWdIUCBTgF2R8rYNVki5"
}
```

## Revoke Token

This will revoke the provided refresh token, if the user is authorized to do so.

```
DELETE /v2/auth/token
```

**Parameters:**

```js
// Content-Type: application/json

{
  token: String // The refresh token to be revoked.
}
```

**Example Request:**

```
curl -X DELETE \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"token": "${REFRESH_TOKEN}"}'
  https://northstar.dosomething.org/v2/auth/token
```

**Example Response:**

```js
// 200 OK

{
  "success": {
    "code": 200,
    "message": "That refresh token has been successfully revoked."
  }
}
```

## Facebook Token Auth

This will check if the given Facebook token is valid & corresponds to the Facebook ID.

```
POST /v2/auth/facebook/validate
```

**Parameters:**

```js
// Content-Type: application/json

{
  // The ID check against.
  facebook_id: String,

  // The access token to verify
  input_token: String,
}
```

**Example Request:**

```
curl -X POST \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"facebook_id": "${FACEBOOK_ID}", "input_token": ${INPUT_TOKEN}}'
  https://northstar.dosomething.org/v2/auth/facebook/validate
```

**Example Response:**

```js
// 200 OK

{
  "success": {
    "code": 200,
    "message": "Verified"
  }
}
```
