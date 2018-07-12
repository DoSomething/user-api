# Authentication Resource

Northstar acts as the authorization server for all of our internal services at DoSomething.org. Individual services may send a user's credentials to Northstar in exchange for a signed access token which can be used throughout our ecosystem.

Access tokens are digitally signed [JSON Web Tokens](http://jwt.io/), which can then be passed between other services and verified _without_ requiring each service to continually ping Northstar for each request. Because access tokens have a short lifetime, the token can be "logged out" of all services by revoking their refresh token.

Each access token includes the authorized user's ID, expiration timestamp, and scopes. Tokens are signed to prevent tampering, and can be verified using a shared public key.

{% hint style="info" %}
**tl;dr:** If a user is logging in to an application and making requests, use the [Authorization Code Grant](authentication-resource.md#create-a-token-authorization-code-grant) to request an access & refresh token for them. If you're performing requests as a "machine" \(not as a direct result of a user's action\), use the Client Credentials Grant.
{% endhint %}

### Create Token \(Authorization Code Grant\)

The authorization code grant allows you to authorize a user without needing to manually handle their username or password. It's a two-step process that involves redirecting the user to Northstar in their web browser, and then using the "code" returned to the application's redirect URL to request an _access_ & _refresh token_.

#### Step One: Authorize the User

To obtain an authorization code, redirect the user to Northstar's `/authorize` page using the following method:

{% api-method method="get" host="https://identity.dosomething.org" path="/authorize" %}
{% api-method-summary %}
Retrieve Authorization Code 
{% endapi-method-summary %}

{% api-method-description %}

{% endapi-method-description %}

{% api-method-spec %}
{% api-method-request %}
{% api-method-path-parameters %}
{% api-method-parameter name="response\_type" type="string" required=true %}
Value of "code".
{% endapi-method-parameter %}

{% api-method-parameter name="client\_id" type="string" required=true %}
Your Client ID.
{% endapi-method-parameter %}

{% api-method-parameter name="destination" type="string" required=false %}
Destination to display on the login page.
{% endapi-method-parameter %}

{% api-method-parameter name="title" type="string" required=false %}
A title to display on the registration page.
{% endapi-method-parameter %}

{% api-method-parameter name="callToAction" type="string" required=false %}
Call to action to display on the registration page.
{% endapi-method-parameter %}

{% api-method-parameter name="coverImage" type="string" required=false %}
Link to a cover image to display on the registration page.
{% endapi-method-parameter %}

{% api-method-parameter name="scope" type="string" required=true %}
Space-delimited list of scopes to request.
{% endapi-method-parameter %}

{% api-method-parameter name="state" type="string" required=true %}
CSRF token that can be validated.
{% endapi-method-parameter %}
{% endapi-method-path-parameters %}
{% endapi-method-request %}

{% api-method-response %}
{% api-method-response-example httpCode=200 %}
{% api-method-response-example-description %}

{% endapi-method-response-example-description %}

```

```
{% endapi-method-response-example %}
{% endapi-method-response %}
{% endapi-method-spec %}
{% endapi-method %}

For example, an application named `puppet-sloth` may initiate a user authorization request like so:

```bash
GET /authorize?response_type=code&client_id=puppet-sloth&scope=user&state=MCceWSE5vHVyYQovh3CL4UWBqe0Uhcpf
```

The user will be presented with a login page \(unless they've previously logged in to Northstar, in which case we'll just use their existing session\), and then redirected back to your application's registered `redirect_uri` with the following values in the query string of the request:

| **Parameter** | **Value** |
| --- | --- | --- |
| `code` | With the authorization code \(used below\). |
| `state` | With the CSRF token \(compare this to what you provided\). |

#### Step Two: Request a Token

You may now use the provided code from step one to request a token:

{% api-method method="post" host="https://identity.dosomething.org" path="/v2/auth/token" %}
{% api-method-summary %}
Create a Token \(Authorization Code Grant\)
{% endapi-method-summary %}

{% api-method-description %}

{% endapi-method-description %}

{% api-method-spec %}
{% api-method-request %}
{% api-method-headers %}
{% api-method-parameter name="Content-Type" type="string" required=true %}
application/json
{% endapi-method-parameter %}

{% api-method-parameter name="Accept" type="string" required=true %}
application/json
{% endapi-method-parameter %}
{% endapi-method-headers %}

{% api-method-body-parameters %}
{% api-method-parameter name="grant\_type" type="string" required=false %}
authorization\_code
{% endapi-method-parameter %}

{% api-method-parameter name="client\_id" type="string" required=true %}
The client application's Client ID.
{% endapi-method-parameter %}

{% api-method-parameter name="client\_secret" type="string" required=true %}
The client application's Client Secret.
{% endapi-method-parameter %}

{% api-method-parameter name="code" type="string" required=true %}
The authorization code returned in Step One.
{% endapi-method-parameter %}
{% endapi-method-body-parameters %}
{% endapi-method-request %}

{% api-method-response %}
{% api-method-response-example httpCode=200 %}
{% api-method-response-example-description %}

{% endapi-method-response-example-description %}

```javascript
{
  "token_type": "Bearer",
  "expires_in": 3600,
  "access_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsImp0aSI6IjU1ZDEwNjk4MTAzNDliMDVhNjdjODI1NDQ2NzIxYmFhNTcyMDM2MTg1MDNhOTlhYjA5ZWYxODVmMGJhZTI2MmJhY2VjZWVhMTY2YjIwYmE5In0.eyJhdWQiOiIiLCJqdGkiOiI1NWQxMDY5ODEwMzQ5YjA1YTY3YzgyNTQ0NjcyMWJhYTU3MjAzNjE4NTAzYTk5YWIwOWVmMTg1ZjBiYWUyNjJiYWNlY2VlYTE2NmIyMGJhOSIsImlhdCI6MTQ2MDQwMDU0NCwibmJmIjoxNDYwNDAwNTQ0LCJleHAiOjE0NjA0MDQxNDQsInN1YiI6IjU0MzBlODUwZHQ4aGJjNTQxYzM3dHQzZCIsInNjb3BlcyI6WyJhZG1pbiJdfQ.Q9SvBEjbJlDEBbBzzvxiL_Dg_nC29Zz34Slrs5WdDdxPKrIwHI6SqnjPvMo4gwoWTr2s9dWye--3Dv0hNNn3xIo7MF6b6DDS96XKplzFRGx2043AzPIVmxxDPz4QdeF18Lnx5W2Aj-_YdRGc-S2n-du2rVYTaGpEzVII4W4Wh7Q",
  "refresh_token": "EytNzc1CJrA0fn1ymUutcg8FzOM7yUER5F+31oP/eRJdXwwaII6Lw4yS/PrC/orThdot4+7o81d/VXdUDBre6NDsMbEtTjk9fJVPDFSU74focg3N0zXKiPziBRvegv4DLrM2RkAfYYfxTK5nM1uMT2pCNBobrA8qHahgmw2XgoSE4J/xco/lmHKP393KMwn0nziKDr0YeqPRi+PAvtdsNPKpydyc0JbAFEevZ2UYXz4bRIaS4nUP+IyB6cYSdnok3OCJr8lDUp/OHA0JlOk9ra7YBFXNB8ZvlR1GEL2qQBlIWCqxPL9xrUBTIWUst7/+imx8LmBqevmGY1UFBXAm7n0p1Ih3Qxj0dx9u5woBdCwLYxAlEL70LaSDbx3qdhF+6uhrZTCnpOPE/tZSImpbmashh/SLtFEMpVP+ifISnLYSnQTvyL4XvWU/8azrFGmDmxYB63kuR4D+4QcqptPyA8JC5sOnn1CpDwzTcn93WMbhtWdIUCBTgF2R8rYNVki5"
}
```
{% endapi-method-response-example %}
{% endapi-method-response %}
{% endapi-method-spec %}
{% endapi-method %}

**Example request in curl:**

```bash
curl -X POST \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"grant_type": "authorization_code", "client_id": "${CLIENT_ID}", "client_secret": "${CLIENT_SECRET}", \
  "code": "KwM/cj40QWuCmpEALcmjxEOeXmcvoYNBQCb7pWd6X0yEG4fRn/b58C8oEos4SRUhSAjOgoZMcKk+rdk9hbd9u5rvFoC3pj8oIFTMyig1fFE0Lpvvu"}' \
  https://northstar.dosomething.org/v2/auth/token
```

### Create Token \(Refresh Token Grant\)

This grant should be used when the access token given by the [Authorization Code Grant](authentication-resource.md#create-token-authorization-code-grant) expires. It will verify the provided refresh token \(given alongside the original access token\) and create a new JWT authentication token. The provided refresh token will be "consumed" and a new refresh token will be returned.

{% api-method method="get" host="https://identity.dosomething.org" path="/v2/auth/token" %}
{% api-method-summary %}
Create a Refresh Token
{% endapi-method-summary %}

{% api-method-description %}

{% endapi-method-description %}

{% api-method-spec %}
{% api-method-request %}
{% api-method-headers %}
{% api-method-parameter name="Content-Type" type="string" required=true %}
application/json
{% endapi-method-parameter %}

{% api-method-parameter name="Accept" type="string" required=true %}
application/json
{% endapi-method-parameter %}
{% endapi-method-headers %}

{% api-method-body-parameters %}
{% api-method-parameter name="grant\_type" type="string" required=true %}
Value of "refresh\_token".
{% endapi-method-parameter %}

{% api-method-parameter name="client\_id" type="string" required=true %}
The client application's Client ID.
{% endapi-method-parameter %}

{% api-method-parameter name="client\_secret" type="string" required=true %}
The client application's Client Secret \(required for "trusted" applications\).
{% endapi-method-parameter %}

{% api-method-parameter name="refresh\_token" type="string" required=true %}
An unused refresh token, returned from the Password Grant.
{% endapi-method-parameter %}

{% api-method-parameter name="scope" type="string" required=false %}
Adjust the scopes for the new access token.
{% endapi-method-parameter %}
{% endapi-method-body-parameters %}
{% endapi-method-request %}

{% api-method-response %}
{% api-method-response-example httpCode=200 %}
{% api-method-response-example-description %}

{% endapi-method-response-example-description %}

```javascript
{
  "token_type": "Bearer",
  "expires_in": 3600,
  "access_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsImp0aSI6IjU1ZDEwNjk4MTAzNDliMDVhNjdjODI1NDQ2NzIxYmFhNTcyMDM2MTg1MDNhOTlhYjA5ZWYxODVmMGJhZTI2MmJhY2VjZWVhMTY2YjIwYmE5In0.eyJhdWQiOiIiLCJqdGkiOiI1NWQxMDY5ODEwMzQ5YjA1YTY3YzgyNTQ0NjcyMWJhYTU3MjAzNjE4NTAzYTk5YWIwOWVmMTg1ZjBiYWUyNjJiYWNlY2VlYTE2NmIyMGJhOSIsImlhdCI6MTQ2MDQwMDU0NCwibmJmIjoxNDYwNDAwNTQ0LCJleHAiOjE0NjA0MDQxNDQsInN1YiI6IjU0MzBlODUwZHQ4aGJjNTQxYzM3dHQzZCIsInNjb3BlcyI6WyJhZG1pbiJdfQ.Q9SvBEjbJlDEBbBzzvxiL_Dg_nC29Zz34Slrs5WdDdxPKrIwHI6SqnjPvMo4gwoWTr2s9dWye--3Dv0hNNn3xIo7MF6b6DDS96XKplzFRGx2043AzPIVmxxDPz4QdeF18Lnx5W2Aj-_YdRGc-S2n-du2rVYTaGpEzVII4W4Wh7Q",
  "refresh_token": "EytNzc1CJrA0fn1ymUutcg8FzOM7yUER5F+31oP/eRJdXwwaII6Lw4yS/PrC/orThdot4+7o81d/VXdUDBre6NDsMbEtTjk9fJVPDFSU74focg3N0zXKiPziBRvegv4DLrM2RkAfYYfxTK5nM1uMT2pCNBobrA8qHahgmw2XgoSE4J/xco/lmHKP393KMwn0nziKDr0YeqPRi+PAvtdsNPKpydyc0JbAFEevZ2UYXz4bRIaS4nUP+IyB6cYSdnok3OCJr8lDUp/OHA0JlOk9ra7YBFXNB8ZvlR1GEL2qQBlIWCqxPL9xrUBTIWUst7/+imx8LmBqevmGY1UFBXAm7n0p1Ih3Qxj0dx9u5woBdCwLYxAlEL70LaSDbx3qdhF+6uhrZTCnpOPE/tZSImpbmashh/SLtFEMpVP+ifISnLYSnQTvyL4XvWU/8azrFGmDmxYB63kuR4D+4QcqptPyA8JC5sOnn1CpDwzTcn93WMbhtWdIUCBTgF2R8rYNVki5"
}
```
{% endapi-method-response-example %}
{% endapi-method-response %}
{% endapi-method-spec %}
{% endapi-method %}

**Example request in curl:**

```bash
curl -X POST \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"grant_type": "refresh_token", "client_id": "${CLIENT_ID}", "client_secret": "${CLIENT_SECRET}", \
  "refresh_token": "${REFRESH_TOKEN}"}' \
  https://northstar.dosomething.org/v2/auth/token
```



