## CallPower Posts

## Create a CallPower Post

```
POST /api/partners/callpower/call
```

This endpoint requires the partner-specific `X-DS-CallPower-API-Key` header.

**Request Parameters:**

```js
{
  // The mobile number of the user who makes the phone call. (required)
  mobile: String,

  // The CallPower campaign_id given by the CallPower system. (required)
  callpower_campaign_id: Int,

  // The status of the call, e.g. completed, busy, failed, no answer, cancelled, unknown. (required)
  status: String,

  // The timestamp of when the call was made, in ISO-8601 format. (required)
  call_timestamp: DateTime,

  // The length of the call (required),
  call_duration: Int,

  //The name of the target the user called.
  campaign_target_name: String,

  // The title of the target the user called. (required)
  campaign_target_title: String,

  // The district of the target the user called.
  campaign_target_district: String,

  // The CallPower campaign name given in CallPower. (required)
  callpower_campaign_name: String,

  // The number the user called to connect to the target. (required)
  number_dialed_into: String,
}
```

<details>
<summary><strong>Example Request</strong></summary>

```sh
curl -X POST "http://northstar.test/api/partners/callpower/call" \
     -H 'X-DS-CallPower-API-Key: ***** Hidden credentials *****' \
     -H 'Accept: application/json' \
     -H 'Content-Type: application/json; charset=utf-8' \
     -d $'{
      "mobile": "+15551231234",
      "status": "completed",
      "call_timestamp": "2017-11-07 18:54:10.829655",
      "call_duration": 36,
      "campaign_target_title": "Representative",
      "campaign_target_district": "FL-7",
      "callpower_campaign_id": 1,
      "campaign_target_name": "Mickey Mouse",
      "callpower_campaign_name": "DefendDreamers_Nov9_CongressCalls",
      "number_dialed_into": "+15554441234"
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
        "message": "Received CallPower payload."
    }
}
```

</details>
