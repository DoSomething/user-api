# Questionnaire Endpoint

The `write` & `activity` scope is required for the create endpoint.

## Store a user's Questionnaire submission

The **Questionnaire Action** block in Phoenix combines multiple **Text Submission Actions** into a single multi-question _Questionnaire_.

Each question is configured with an individual Action ID so that we can store each questions response into an individual Post.

We store some metadata in the Post's `details` field to qualify that these Posts are via a Questionnaire block, and to tie the submission to its parent Contentful block & question title: e.g. `details: { questionnaire: true, question: [question title], contentful_id: [questionnaire Contentful ID] }`

```
POST /api/v3/questionnaires
```

**Request Parameters:**

```js
{
  /* The list of questionnaire questions containing a few required properties listed below. */
  questions: Array;

  /* The question title. */
  questions[title]: String;

  /* The user submission. */
  questions[answer]: String;

  /* The Action ID associated with this question. */
  questions[action_id]: Number;

  /* The Contentful ID of the Questionnaire block. */
  contentful_id: String;

  /* This endpoint accepts the other standard parameters for POSTing to the `/api/v3/posts` endpoint which it will use to create the individual Posts. */
}
```

<details>
<summary><strong>Example Request</strong></summary>

```sh
  curl -X "POST" "http://northstar.test/api/v3/questionnaires" \
     -H 'Accept: application/json' \
     -H 'Content-Type: application/json; charset=utf-8' \
     -d $'{
  "questions": [
    { "action_id": 1, "title": "Who inspires you?", "answer": "My cat." },
    { "action_id": 2, "title": "Why?", "answer": "They rock." },
  ],
  "contentful_id": "1a2b3c"
}'
```

</details>

<details>
<summary><strong>Example Response</strong></summary>

```json
// 200 OK

{
  "data": [
    {
      "id": 1,
      "signup_id": 2,
      "type": "text",
      "action": "Quia Reprehenderit Aut",
      "action_id": 1,
      "campaign_id": "1",
      "media": {
        "url": null,
        "original_image_url": null,
        "text": "My cat."
      },
      "quantity": null,
      "hours_spent": null,
      "reactions": {
        "reacted": false,
        "total": null
      },
      "status": "pending",
      "location": null,
      "location_name": null,
      "created_at": "2021-03-16T20:24:29+00:00",
      "updated_at": "2021-03-16T20:24:29+00:00",
      "cursor": "MTY2OA==",
      "northstar_id": "123",
      "tags": [],
      "source": "dev-oauth",
      "source_details": null,
      "remote_addr": "0.0.0.0",
      "details": "{\"questionnaire\":true,\"question\":\"Who inspires you?\",\"contentful_id\":\"1a2b3c\"}",
      "referrer_user_id": null,
      "group_id": null,
      "school_id": null,
      "club_id": null,
      "action_details": {
        "data": {
          "id": 2,
          "name": "Quia Reprehenderit Aut",
          "campaign_id": 1,
          "post_type": "text",
          "post_label": "Text Post",
          "action_type": "share-something",
          "action_label": "Share Something",
          "time_commitment": "<0.083",
          "time_commitment_label": "< 5 minutes",
          "callpower_campaign_id": null,
          "reportback": true,
          "civic_action": true,
          "scholarship_entry": true,
          "collect_school_id": true,
          "volunteer_credit": false,
          "anonymous": false,
          "online": false,
          "quiz": false,
          "noun": "things",
          "verb": "done",
          "created_at": "2021-03-16T16:03:49+00:00",
          "updated_at": "2021-03-16T16:03:49+00:00"
        }
      }
    },
    {
      "id": 2,
      "signup_id": 2,
      "type": "text",
      "action": "Quia Reprehenderit Aut",
      "action_id": 1,
      "campaign_id": "1",
      "media": {
        "url": null,
        "original_image_url": null,
        "text": "They rock."
      },
      "quantity": null,
      "hours_spent": null,
      "reactions": {
        "reacted": false,
        "total": null
      },
      "status": "pending",
      "location": null,
      "location_name": null,
      "created_at": "2021-03-16T20:24:29+00:00",
      "updated_at": "2021-03-16T20:24:29+00:00",
      "cursor": "MTY2OA==",
      "northstar_id": "123",
      "tags": [],
      "source": "dev-oauth",
      "source_details": null,
      "remote_addr": "0.0.0.0",
      "details": "{\"questionnaire\":true,\"question\":\"Why?\",\"contentful_id\":\"1a2b3c\"}",
      "referrer_user_id": null,
      "group_id": null,
      "school_id": null,
      "club_id": null,
      "action_details": {
        "data": {
          "id": 3,
          "name": "Quia Reprehenderit Aut Two",
          "campaign_id": 1,
          "post_type": "text",
          "post_label": "Text Post",
          "action_type": "share-something",
          "action_label": "Share Something",
          "time_commitment": "<0.083",
          "time_commitment_label": "< 5 minutes",
          "callpower_campaign_id": null,
          "reportback": true,
          "civic_action": true,
          "scholarship_entry": true,
          "collect_school_id": true,
          "volunteer_credit": false,
          "anonymous": false,
          "online": false,
          "quiz": false,
          "noun": "things",
          "verb": "done",
          "created_at": "2021-03-16T16:03:49+00:00",
          "updated_at": "2021-03-16T16:03:49+00:00"
        }
      }
    }
  ]
}
```

</details>
