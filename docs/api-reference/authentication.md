# Authentication

We handle authentication using **OAuth 2**, an [open standard](https://tools.ietf.org/html/rfc6749) for authorization. OAuth allows us to issue access tokens \(so a user or machine's credentials don't need to be sent with every request\) and refresh tokens \(so that a user's credentials do not need to be stored to re-authorize later\). Most importantly, Northstar access tokens can be used to make requests to _any_ other DoSomething.org service!

**Here's the tl;dr:** If a user is logging in to an application and making requests, use the [Authorization Code grant](https://github.com/DoSomething/northstar/tree/79cbc752bd6fd475478d7538f311bc5d27ed4cb7/docs/api-reference/endpoints/auth.md#create-token-authorization-code-grant) to request an access & refresh token for them. If you're performing requests as a "machine" \(not as a direct result of a user's action\), use the [Client Credentials Grant](https://github.com/DoSomething/northstar/tree/79cbc752bd6fd475478d7538f311bc5d27ed4cb7/docs/api-reference/endpoints/auth.md#create-token-client-credentials-grant).

## Clients

When requesting an authentication token, credentials for a valid **client application** must be included. This includes a client ID \(for example, `phoenix` or `letsdothis-ios`\) and a client secret, which is like a long "password" for that application.

Sorry, there's no public API access... yet!

## Scopes

Authentication tokens are granted **scopes** to limit their privileges. This allows us to limit the abilities of "untrusted" clients that operate over a public network like the [mobile app](https://app.dosomething.org), and limit the damage that can be done if a client is compromised.

Each client has a list of whitelisted scopes, and scopes that are not allowed for a client will trigger an error if requested. For example, a malicious user with the mobile app client credentials could never request the ability to delete users.

The allowed scopes for each client are listed on Aurora. A machine-friendly list of scopes and their descriptions can be retrieved from the public [`scopes`](https://github.com/DoSomething/northstar/tree/79cbc752bd6fd475478d7538f311bc5d27ed4cb7/docs/api-reference/endpoints/clients.md#retrieve-all-client-scopes) endpoint.

## Access Tokens

The [Authorization Code Grant](https://github.com/DoSomething/northstar/tree/79cbc752bd6fd475478d7538f311bc5d27ed4cb7/docs/api-reference/endpoints/auth.md#create-token-authorization-code-grant) may be used to request an **access token** which can "authorize" requests on behalf of a particular user.

We authenticate requests to our APIs using [JSON Web Tokens](https://jwt.io), another [open standard](https://tools.ietf.org/html/rfc7519) that allows us to issue cryptographically signed tokens. Because the tokens are signed, this data can't be tampered with without invalidating the signature, and any resource server can validate a token using Northstar's public key _without_ making an HTTP request!

Here's an annotated payload from an example access token:

```javascript
{
  // Issuer: the authorization server URL.
  "iss": "https://northstar.dosomething.org",

  // Audience: the client which requested this token. 
  "aud": "phoenix",

  // JWT ID: a unique identifier for the JWT
  "jti": "6feda42e0d11ef7c3924ca711017645b3bab01d2ed80e63d7f6a84b2c31fcfdaaf77d33aed6755d6",

  // Issued At: the time at which the JWT was issued.
  "iat": 1465487055,

  // Not Before: the time before which the token MUST NOT be accepted.
  "nbf": 1465487055,

  // Expiration Time: the time on or after which the token MUST NOT be accepted.
  "exp": 1465490655,

  // Subject: the Northstar ID of the user that is authorized by this JWT.
  "sub": "5430e850dt8hbc541c37tt3d",

  // Role: the user's role (e.g. 'user', 'staff', 'admin')
  "role": "user",

  // Scopes: the privileges this key authorizes the client to act with.
  "scopes": [
    "user"
  ]
}
```

If you're curious about more of the nerdy details on JWTs, check out the [official introduction](https://jwt.io/introduction/).

## Refresh Tokens

Access tokens are short-lived and expire after an hour. Since that's not a very long time, clients are also issued **refresh tokens** which can be used \(once!\) to create another access token. Refresh tokens don't expire, but can be revoked manually - for example, if a user "removes" an application or logs out from their account.

Once a refresh token is used to create a new access token through the [Refresh Token Grant](https://github.com/DoSomething/northstar/tree/79cbc752bd6fd475478d7538f311bc5d27ed4cb7/docs/api-reference/endpoints/auth.md#create-token-refresh-token-grant), a _new_ access token and refresh token are returned & the old refresh token cannot be used again.

## Machine Authentication

Some services don't authenticate on behalf of a user \(for example, an internal batch-processing service\). These clients can use the [Client Credentials Grant](https://github.com/DoSomething/northstar/tree/79cbc752bd6fd475478d7538f311bc5d27ed4cb7/docs/api-reference/endpoints/auth.md#create-token-client-credentials-grant) to request a signed authentication token that securely identifies that particular application. Since user credentials are not involved, this does _not_ create a refresh token.

## Making Authenticated Requests

Access tokens can be used to make authorized requests to Northstar and other DoSomething.org services. When making requests, the token should be be provided as the `Authorization` header of a request:

```bash
GET /v1/users
Authorization: Bearer xxxxxxx
```

