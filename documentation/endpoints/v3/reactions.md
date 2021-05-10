## Reactions

All `v3 /posts/{post}/reactions` endpoints require the `activity` scope. `Create`/`update`/`delete` endpoints also require the `write` scope.

```
## Create or update a Reaction
```

POST /api/v3/post/:post_id/reactions

```
Example Request
```

curl http://rogue.dev:8000/api/v3/posts/:post_id/reactions
--header "Accept: application/json"
--X-DS-Rogue-API-Key "API_KEY"

```
Example Response
```

{
"data": {
"northstar_id": "5589c991a59dbfa93d8b45ae",
"post_id": "61",
"created_at": "2017-10-24T14:57:40+00:00",
"updated_at": "2017-11-03T17:00:51+00:00",
"deleted_at": null
},
"meta": {
"action": "liked",
"total_reactions": 1
}
}

````
## Retrieve all reactions of a post

```
GET /api/v3/posts/:post_id/reactions
```
Example Response:

```
{
    "data": [
        {
            "northstar_id": "5589c991a59dbfa93d8b45ae",
            "post_id": "61",
            "term": {
                "name": "heart",
                "id": 641,
                "total": 1
            },
            "created_at": "2017-10-24T14:57:40+00:00",
            "updated_at": "2017-11-03T17:00:51+00:00",
            "deleted_at": null
        },
        {
            "northstar_id": "5589c991a59dbfa93d8b45aea",
            "post_id": "61",
            "term": {
                "name": "heart",
                "id": 641,
                "total": 1
            },
            "created_at": "2017-11-03T18:02:55+00:00",
            "updated_at": "2017-11-03T18:02:55+00:00",
            "deleted_at": null
        },
        {
            "northstar_id": "5589c991a59dbfa93d8b45aeas",
            "post_id": "61",
            "term": {
                "name": "heart",
                "id": 641,
                "total": 1
            },
            "created_at": "2017-11-03T18:03:01+00:00",
            "updated_at": "2017-11-03T18:03:01+00:00",
            "deleted_at": null
        },
    ]
}
```
````
