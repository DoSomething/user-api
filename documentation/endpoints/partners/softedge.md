## SoftEdge Posts

## Create a SoftEdge Post

```
POST /api/partners/softedge/email
```

This endpoint requires the partner-specific `X-DS-SoftEdge-API-Key` header.

**Request Parameters:**

```js
{
  // The `id` of action the post is associated to. (required)
  action_id: Int,

  // The `id` of the user who sent the email. (required)
  northstar_id: String,

  // The timestamp of when the email was sent. (required)
  email_timestamp: DateTime,

  // The name of the target the user emailed. (required)
  campaign_target_name: String,

  // The title of the target the user emailed.
  campaign_target_title: String,

  // The district of the target the user emailed.
  campaign_target_distrct: String,
}
```

<details>
<summary><strong>Example Request</strong></summary>

```sh
curl -X POST "http://northstar.test/api/partners/softedge/email" \
     -H 'X-DS-SoftEdge-API-Key: ***** Hidden credentials *****' \
     -H 'Accept: application/json' \
     -H 'Content-Type: application/json; charset=utf-8' \
     -d $'{
      "action_id": 1,
      "northstar_id": "609d4e6cc166170977230222",
      "call_timestamp": "2017-11-07 18:54:10.829655",
      "campaign_target_district": "FL-7",
      "campaign_target_name": "Mickey Mouse",
      "campaign_target_title": "Representative"
    }'
```

</details>

<details>
<summary><strong>Example Response</strong></summary>

```js
// 200 OK

{
    "success": {
        "code": 200,
        "message": "Received SoftEdge payload."
    }
}
```

</details>
