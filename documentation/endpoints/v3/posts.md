## Posts

All `v3 /posts` endpoints require the `activity` scope. `Create`/`update`/`delete` endpoints also require the `write` scope.

Only admins and post owners will have `tags`, `source`, `remote_addr` (which will be `0.0.0.0` for all posts in compliance with GDPR), and hidden posts (posts that are tagged 'Hide In Gallery') returned in the response.

Anonymous requests will only return posts with status `accepted`, `register-form`, or `register-OVR`.

Logged-in users can additionally see any of their own posts with any status, and any voter-reg post (with any status) that they have referred.

Staff can see all posts.

If the post's action is marked as "anonymous", the `northstar_id` field will only be returned for the owner.

## Retrieve All Posts

```
GET /api/v3/posts
```

Posts are returned in reverse chronological order.

### Optional Query Parameters

- **limit**
  - Set the number of records to return in a single response.
  - e.g. `/posts?limit=35`
- **page** _(integer)_
  - For pagination, specify page of activity to return in the response.
  - e.g. `/posts?page=2`
- **filter[signup_id]** _(integer)_
  - The signup ID to filter the response by.
  - e.g. `/posts?filter[signup_id]=47`
- **filter[campaign_id]** _(integer)_
  - The campaign ID to filter the response by.
  - e.g. `/posts?filter[campaign_id]=47`
- **filter[group_id]** _(integer)_
  - The group ID to filter the response by.
  - e.g. `/posts?filter[group_id]=11`
- **filter[northstar_id]** _(string)_
  - The northstar_id to filter the response by.
  - e.g. `/posts?filter[northstar_id]=5554eac1a59dbf117e8b4567`
- **filter[status]** _(string)_
  - The status(es) to filter the response by.
  - e.g. `/posts?filter[status]=accepted,pending`
- **filter[type]** _(string)_
  - The type to filter the response by.
  - e.g. `/posts?filter[type]=photo,voter-reg`
- **filter[action]** _(string)_
  - The action to filter the response by.
  - e.g. `/posts?filter[action]=action-1`
- **filter[action_id]** _(string)_
  - The action id to filter the response by.
  - e.g. `/posts?filter[action_id]=1`
- **filter[created_at]** _(timestamp)_
  - The created_at date to filter the response by.
  - e.g. `/posts?filter[created_at]=2017-04-28 01:46:45`
- **filter[exclude]** _(integer)_
  - The post id(s) to exclude in response.
  - e.g. `/posts?filter[exclude]=2,3,4`
- **filter[tag]** _(string)_
  - The tag(s) to filter the response by.
  - Tag is passed in as tag_slug.
  - e.g. `/posts?filter[tag]=good-submission,good-for-sponsor`
- **filter[source]** _(string)_
  - The source(s) to filter the response by.
  - e.g. `/posts?filter[source]=sms`
- **filter[referrer_user_id]** _(string)_
  - The referrer_user_id to filter the response by.
  - e.g. `/posts?filter[referrer_user_id]=581ba6dd7f43c26c6d2349d3`
- **filter[location]** _(string)_
  - The location to filter the response by.
  - e.g. `/posts?filter[location]=US-NY`
- **filter[volunteer_credit]** _(boolean)_
  - Filter for posts (un)qualified for volunteer credit (where the associated action's volunteer_credit field is true/false.)
  - e.g. `/posts?filter[volunteer_credit]=true`
- **include** _(string)_
  - Include additional related records in the response: `signup`, `siblings`
  - e.g. `/posts?include=signup,siblings`
  - Note: siblings will only load up to 5 siblings (for performance reasons).

Example Response:

```
{
    "data": [
        {
            "id": 2984,
            "signup_id": 4673,
            "type": "photo",
            "action": "default",
            "action_id": 1,
            "northstar_id": "123",
            "media": {
                "url": "http://rogue.test/images/914",
                "original_image_url": "http://rogue.test/storage/uploads/reportback-items/476-923df5957838355206574f72d5520f0f-1548449285.jpeg?time=1548449285",
                "text": null
            },
            "quantity": "12",
            "reactions": {
                "reacted": true,
                "total": 2
            },
            "status": "accepted",
            "location": "US-NY",
            "location_name": "New York",
            "school_id": null,
            "referrer_user_id": null,
            "group_id": null,
            "created_at": "2016-11-30T21:21:24+00:00",
            "updated_at": "2017-08-02T14:11:26+00:00"
        },
        {
            "id": 3655,
            "signup_id": 5787,
            "type": "photo",
            "action": "default",
            "action_id": 1,
            "northstar_id": "1234",
            "media": {
                "url": "https://s3.amazonaws.com/ds-rogue-qa/uploads/reportback-items/edited_3655.jpeg",
                "original_image_url": "http://rogue.test/storage/uploads/reportback-items/476-923df5957838355206574f72d5520f0f-1548449285.jpeg?time=1548449285",
                "text": "Perhaps you CAN be of some assistance, Bill"
            },
            "quantity": "12",
            "reactions": {
                "reacted": false,
                "total": 8
            },
            "status": "accepted",
            "location": "US-NY",
            "location_name": "New York",
            "school_id": null,
            "referrer_user_id": null,
            "group_id": null,
            "created_at": "2016-02-10T16:19:25+00:00",
            "updated_at": "2017-08-02T14:11:35+00:00"
            "action_details": {
              "data": {
                "id": 1,
                "name": "default",
                "campaign_id": 9009,
                "post_type": "photo",
                "reportback": true,
                "civic_action": true,
                "scholarship_entry": true,
                "anonymous": false,
                "noun": "things",
                "verb": "done",
                "created_at": "2019-02-20T21:42:01+00:00",
                "updated_at": "2019-02-20T21:42:01+00:00"
              }
            }
          }
    ],
    "meta": {
        "cursor": {
        "current": 1,
        "prev": null,
        "next": "http://rogue.test/api/v3/posts?page=2",
        "count": 20
      }
    }
}
```

## Retrieve A Specific Post

```
GET /api/v3/posts/:post_id
```

Example Response:

```
{
  "data": {
    "id": 871,
    "signup_id": 1330,
    "type": "photo",
    "action": "default",
    "action_id": 1,
    "northstar_id": "123",
    "media": {
      "url": "http://rogue.test/images/871",
      "original_image_url": "http://rogue.test/storage/uploads/reportback-items/63517-676c9c02029df581d3668ae869d937b8-1548272527.jpeg?time=1548449546",
      "text": "Sequi praesentium labore sed velit et velit pariatur."
      },
    "quantity": 23,
    "reactions": {
    "reacted": false,
    "total": 0
    },
    "status": "accepted",
    "location": "US-NY",
    "location_name": "New York",
    "school_id": "3600052",
    "referrer_user_id": null,
    "group_id": null,
    "created_at": "2019-01-23T19:42:07+00:00",
    "updated_at": "2019-01-23T19:42:07+00:00"
    "action_details": {
      "data": {
        "id": 1,
        "name": "default",
        "campaign_id": 9009,
        "post_type": "photo",
        "reportback": true,
        "civic_action": true,
        "scholarship_entry": true,
        "anonymous": false,
        "noun": "things",
        "verb": "done",
        "created_at": "2019-02-20T21:42:01+00:00",
        "updated_at": "2019-02-20T21:42:01+00:00"
      }
    }
  }
}
```

## Create a Post

This will automatically create or update the corresponding signup.

```
POST /api/v3/posts
```

Required params:

- **northstar_id**: (string)
  The `northstar_id` of the user the post belongs to.
- **action_id**: (int) required without action or campaign_id.
  The action ID of the action that the user's post is associated with.
- **type**: (string) required.
  The type of post submitted. Must be one of the following types: `photo`, `voter-reg`, `text`, `share-social`, `phone-call`. `share-social` posts will be auto-accepted unless an admin sets a custom `status`.
- **file**: (multipart/form-data) required for `photo` posts.
  File to save of post image. Dimension requirements: min width: 400, min height: 400, max width: 5000, max height: 4000.

Optional params:

- **quantity**: (int|nullable).
  The number of reportback nouns verbed. Can be `null`.
- **why_participated**: (string).
  The reason why the user participated.
- **text**: (string).
  Corresponding text for the post (could be photo caption or other words). 500 max characters.
- **status**: (string).
  Option to set status upon creation if admin uploads post for user.
- **location** (string).
  The The ISO 3166-2 region code this was submitted from, e.g. `US-NY`.
- **school_id** (string).
  The school ID this post should be associated with.
- **details** (json).
  A JSON field to store extra details about a post.
- **referrer_user_id** (string).
  The referring User ID that this post should be associated with.
- **group_id** (int).
  The Group ID that this post should be associated with.
- **dont_send_to_blink** (boolean).
  If included and true, the data for this Post will not be sent to Blink.
- **created_at** (timestamp).
  If admin and included, the timestamp to set the `created_at` date to. This would be used in a case when we're importing data from the importer app (e.g. `voter-reg` posts, historical FB share posts, etc.).

Deprecated params:

- **campaign_id**: (int) required without action_id.
  The Campaign ID of the campaign that the user's post is associated with.
- **action**: (string) required without action_id.
  Describes the bucket the action is tied to. A campaign could ask for multiple types of actions throughout the life of the campaign.

Example Response:

```
{
  "data": {
    "id": 96,
    "signup_id": 141,
    "type": "text",
    "action": "default",
    "action_id": 1
    "northstar_id": "123",
    "media": {
        "url": null,
        "original_image_url": "?time=1544132655",
        "text": "to test"
    },
    "quantity": "6",
    "reactions": {
        "reacted": false,
        "total": null
    },
    "status": "pending",
    "location": "US-NY",
    "location_name": "New York",
    "school_id": "3600052",
    "referrer_user_id": "559442cca59dbfca578b4bed",
    "created_at": "2018-12-06T21:44:15+00:00",
    "updated_at": "2018-12-06T21:44:15+00:00",
    "tags": [],
    "source": "dev-oauth",
    "source_details": null,
    "remote_addr": "0.0.0.0",
    "details": null,
    "signup": {
      "data": {
        "id": 141,
        "northstar_id": "123",
        "campaign_id": "1173",
        "campaign_run_id": null,
        "quantity": 6,
        "created_at": "2018-12-06T21:44:14+00:00",
        "updated_at": "2018-12-06T21:44:15+00:00",
        "why_participated": null,
        "source": "dev-oauth",
        "source_details": null,
        "details": null,
        "referrer_user_id: "559442cca59dbfca578b4bed",
        "user": {
          "data": {
              "first_name": "Santa",
              "last_name": "Claus",
              "birthdate": null,
              "email": "email@email.com",
              "mobile": "6179093031"
          }
        }
      }
    },
    "action_details": {
      "data": {
        "id": 1,
        "name": "default",
        "campaign_id": 1173,
        "post_type": "text",
        "reportback": true,
        "civic_action": true,
        "scholarship_entry": true,
        "anonymous": false,
        "noun": "things",
        "verb": "done",
        "created_at": "2019-02-20T21:42:01+00:00",
        "updated_at": "2019-02-20T21:42:01+00:00"
      }
    }
  }
}
```

## Delete a Post

```
DELETE /api/v3/posts/:post_id
```

Example Response:

```
{
    "code": 200,
    "message": "Post deleted."
}
```

## Update a Post

```
PATCH /api/v3/posts/:post_id
```

- **text**: (string)
  The text of the post.
- **quantity**: (int)
  The quantity of items in the post.

Example request body:

```
[
  "text" => "Here is a brand new caption"
  "quantity" => "7"
]
```

Example response:

```
{
  "data": {
      "id": 332,
      "signup_id": 289,
      "type": "text",
      "action": "default",
      "action_id": 1
      "northstar_id": "5589c991a59dbfa93d8b45ae",
      "media": {
          "url": "http://localhost/storage/uploads/reportback-items/edited_332.jpeg?time=1509379493",
          "original_image_url": "http://localhost/storage/uploads/reportback-items/289-923df5957838355206574f72d5520f0f-1509115822.jpeg?time=1509379493",
          "text": "Here is a brand new caption"
      },
      "quantity": "7",
      "tags": [],
      "reactions": {
          "reacted": false,
          "total": null
      },
      "status": "accepted",
      "location": "US-MN",
      "location_name": "Minnesota",
      "source": "rogue-admin",
      "remote_addr": "0.0.0.0",
      "created_at": "2017-10-27T14:50:22+00:00",
      "updated_at": "2017-10-30T16:04:53+00:00"
      "action_details": {
        "data": {
          "id": 1,
          "name": "default",
          "campaign_id": 9009,
          "post_type": "text",
          "reportback": true,
          "civic_action": true,
          "scholarship_entry": true,
          "anonymous": false,
          "noun": "things",
          "verb": "done",
          "created_at": "2019-02-20T21:42:01+00:00",
          "updated_at": "2019-02-20T21:42:01+00:00"
        }
      }
    }
  }
}
```
