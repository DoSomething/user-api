---
description: This section provides documentation on the available Northstar API endpoints.
---

# Overview

This is the Northstar API, which is used for collecting and distributing user and activity information solely to our connected platforms.

* [Authentication](authentication.md)
* [Northstar API Version 1.0](api-v1/)
* [Northstar API Version 2.0](api-v2/)

## Responses

We provide standard response formatting for all resource types using [Transformers](https://github.com/DoSomething/northstar/tree/master/app/Http/Transformers).

### Resources

All resources are returned within a `data` property on the response. For endpoints that return a collection, this property will be an array. Responses will include all properties available to the given client/user, specified as `null` if they do not exist on that particular item.

Pagination & other meta-information may be provided in a `meta` key on the response.

For example:

```javascript
{
    "data": [
      { /* ... */ },
      { /* ... */ },
    ],
    "meta": {
        "pagination": {
            "total": 60,
            "count": 20,
            "per_page": 20,
            "current_page": 1,
            "total_pages": 3,
            "links": {
                "next": "http://northstar.dosomething.org/v1/users?page=2"
            }
    }
}
```

### Errors & Status Codes

Northstar returns standard HTTP status codes to indicate how a request turned out. In general, `2xx` codes are returned on successful requests, `4xx` codes indicate an error in the request, and `5xx` error codes indicate an unexpected problem on the API end.

| **Code** | **Meaning** |
| --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- |
| 200 | **Okay** – Everything is awesome. |
| 400 | **Bad Request** – The request has incorrect syntax. |
| 401 | **Unauthorized** – The given credentials are invalid or you are not authorized to view that resource. |
| 403 | **Forbidden** – \(For legacy authentication _only_.\) The authenticated user doesn't have the proper privileges. |
| 404 | **Not Found** – The specified resource could not be found. |
| 418 | **I'm a teapot** – The user [needs more caffeine](https://www.ietf.org/rfc/rfc2324.txt). |
| 422 | **Unprocessable Entity** – The request couldn't be completed due to validation errors. See the `error.fields`property on the response. |
| 429 | **Too Many Requests** – The user/client has sent too many requests in the past minute. See [Rate Limiting](overview.md#rate-limiting). |
| 500 | **Internal Server Error** – Northstar has encountered an internal error. Please [make a bug report](https://github.com/DoSomething/northstar/issues/new) with as much detail as possible about what led to the error! |
| 503 | **Service Unavailable** – Northstar is temporarily unavailable. |

We return a standard `error` response on all errors that should provide a human-readable explanation of the problem:

```javascript
{
    "error": {
        "code": 418,
        "message": "Tea. Earl Grey. Hot."
        
        // For 422 Unprocessable Entity, the "fields" object has specific validation errors:
        "fields": {
          "email": ["The email must be a valid email address."],
          "mobile": ["The mobile has already been taken."]
        }
    },
    // When running locally, debug information will be included in the response:
    "debug": {
        "file": "/home/vagrant/sites/northstar/app/Http/Controllers/UserController.php",
        "line": 115
    }
}
```

OAuth authentication errors are formatted slightly differently \(to conform to [the OAuth spec](https://tools.ietf.org/html/rfc6749#section-5.2)\):

```javascript
{
  // A machine-readable error code.
  "error": "access_denied", "invalid_request", "invalid_client", "invalid_grant", "unauthorized_client", "unsupported_grant_type", "invalid_scope",
  
  // A human readable explanation of the problem.
  "message": "...",
  
  // Optionally, more specific details on the issue.
  "hint": "..."
}
```

### Rate Limiting

Authentication and registration attempts are rate limited to prevent abuse. Users are limited by IP address to 10 logins or registrations per 15 minutes, and 10 failed client authentication attempts.

The currently applied rate limit and remaining requests are returned as headers on each response:

| **Header** | **Description** |
| --- | --- | --- | --- |
| `X-RateLimit-Limit` | The maximum number of requests that this client may make per hour. |
| `X-RateLimit-Remaining` | The number of requests remaining of your provided limit. |
| `Retry-After` | If rate limit is exceeded, this is the amount of time until you may make another request. |

### Libraries

You can use **Gateway**, our standard API client, in [PHP](https://github.com/DoSomething/gateway) or [JavaScript](https://github.com/DoSomething/gateway-js) applications.

![DoSomething Bot](../.gitbook/assets/dsbot.png)

