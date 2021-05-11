## Actions

These endpoints require the role `admin` or `staff` to use.

## Retrieve All Actions (created in Rogue)

```
GET /api/v3/actions
```

### Optional Query Parameters

- **filter[column]** _(string)_

  - Filter results by the given column: `id`, `campaign_id`, `callpower_campaign_id`
  - You can filter by more than one value for a column, e.g. `/actions?filter[id]=121,122`

- **include** _(string)_
  - Include additional related records and/or computed attributes in the response: `accepted_quantity`
  - e.g. `api/v3/actions?include=accepted_quantity`

Example Response:

```
{
    "data": [
        {
            "id": 11,
            "name": "august-2018-turbovote",
            "campaign_id": 9005,
            "post_type": "voter-reg",
            "post_label": "Voter Registration",
            "action_type": null,
            "action_label": null,
            "time_commitment": null,
            "time_commitment_label": null,
            "callpower_campaign_id": null,
            "reportback": true,
            "civic_action": true,
            "scholarship_entry": false,
            "collect_school_id": false,
            "volunteer_credit": false,
            "anonymous": false,
            "online": false,
            "quiz": false,
            "impact_goal": 4000,
            "noun": "votes",
            "verb": "registered",
            "created_at": "2019-01-23T21:23:42+00:00",
            "updated_at": "2019-01-23T21:23:42+00:00"
        },
        {
            "id": 12,
            "name": "december-2018-turbovote",
            "campaign_id": 9005,
            "post_type": "voter-reg",
            "post_label": "Voter Registration",
            "action_type": null,
            "action_label": null,
            "time_commitment": null,
            "time_commitment_label": null,
            "callpower_campaign_id": null,
            "reportback": true,
            "civic_action": true,
            "scholarship_entry": false,
            "collect_school_id": false,
            "anonymous": false,
            "online": false,
            "quiz": false,
            "impact_goal": 10000,
            "noun": "votes",
            "verb": "registered",
            "created_at": "2019-01-23T21:23:42+00:00",
            "updated_at": "2019-01-23T21:23:42+00:00"
        }
    ],
    "meta": {
        "pagination": {
            "total": 2,
            "count": 2,
            "per_page": 20,
            "current_page": 1,
            "total_pages": 1,
            "links": []
        }
    }
}
```

## Retrieve A Specific Action

### Optional Query Parameters

- **include** _(string)_
  - Include additional related records and/or computed attributes in the response: `accepted_quantity`
  - e.g. `api/v3/actions/:action_id/?include=accepted_quantity`

```
GET /api/v3/actions/:action_id
```

Example Response:

```
{
  "data": {
  "id": 8,
  "name": "action-page",
  "campaign_id": 9003,
  "post_type": "share-social",
  "post_label": "Social Share",
  "action_type": "share-something",
  "action_label": "Share Something",
  "time_commitment": "<0.5",
  "time_commitment_label": "< 30 minutes",
  "callpower_campaign_id": null,
  "reportback": false,
  "civic_action": false,
  "scholarship_entry": false,
  "collect_school_id": false,
  "volunteer_credit": false,
  "anonymous": false,
  "online": true,
  "quiz": false,
  "impact_goal": 250000,
  "noun": "resources",
  "verb": "shared",
  "created_at": "2019-01-23T21:23:42+00:00",
  "updated_at": "2019-01-23T21:23:42+00:00"
  }
}
```
