## Reviews

Update a post's status when an admin reviews the post.

```
POST /api/v3/posts/:post_id/reviews
```

- **status**: (string) required.
  The status of the post.
- **comment**: (string)
  A comment that the reviewer has made.

Example Response:

```
{
    "data": {
        "id": 28,
        "signup_id": 7,
        "northstar_id": "55882c57a59dbfa93d8b4599",
        "media": {
            "url": "http://rogue.test/images/28",
            "original_image_url": "http://rogue.test/storage/uploads/reportback-items/29624-6872c640fe5b2b151ebe62f2537a0cfb-1516983917.jpeg?time=1516992660",
            "caption": "Laborum earum iure magni maiores cumque."
        },
        "quantity": 5,
        "tags": [],
        "reactions": {
            "reacted": false,
            "total": null
        },
        "status": "rejected",
        "source": "phoenix-next",
        "remote_addr": "0.0.0.0",
        "created_at": "2018-01-26T16:25:17+00:00",
        "updated_at": "2018-01-26T18:51:00+00:00"
    }
}
```
