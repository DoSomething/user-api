---
description: This section provides documentation on the available Northstar API endpoints.
---

# Overview

This is the Northstar API, which is used for collecting and distributing user and activity information solely to our connected platforms.

* [Authentication](authentication.md)
* [Northstar API Version 1.0](api-v1/)
* [Northstar API Version 2.0](api-v2/)

## Responses

We provide standard response formatting for all resource types using Transformers.

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

| Code | Meaning |
| --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- |
| 200 | **Okay** – Everything is awesome. |
| 400 | **Bad Request** – The request has incorrect syntax. |
| 401 | **Unauthorized** – The given credentials are invalid or you are not authorized to view that resource. |
| 403 | **Forbidden** – \(For legacy authentication _only_.\) The authenticated user doesn't have the proper privileges. |
| 404 | **Not Found** – The specified resource could not be found. |
| 418 | **I'm a teapot** – The user [needs more caffeine](https://www.ietf.org/rfc/rfc2324.txt). |
| 422 | **Unprocessable Entity** – The request couldn't be completed due to validation errors. See the `error.fields`property on the response. |
| 429 | **Too Many Requests** – The user/client has sent too many requests in the past minute. See Rate Limiting. |
| 500 | **Internal Server Error** – Northstar has encountered an internal error. Please [make a bug report](https://github.com/DoSomething/northstar/issues/new) with as much detail as possible about what led to the error! |
| 503 | **Service Unavailable** – Northstar is temporarily unavailable. |

![DoSomething Bot](../.gitbook/assets/dsbot.png)

