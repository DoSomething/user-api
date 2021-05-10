## Action Stats

Action stats are used to store aggregate calculations for an action and a school. They are created or updated per action and school when a post that contains a school ID is reviewed.

## Retrieve All Action Stats

```
GET /api/v3/action-stats
```

### Optional Query Parameters

- **filter[column]** _(string)_

  - Filter results by column. Supported columns: `action_id`, `location`, `school_id`
  - Use commas to filter by multiple values in a column, e.g. `/action-stats?filter[action_id]=121,122`

- **orderBy** _(string)_
  - Order results by column. Supported columns: `id`, `impact`
  - e.g. `/action-stats?orderBy=impact,desc`

Example Response:

```
{
  "data": [
    {
      "id": 1,
      "action_id": 1,
      "school_id": "3401457",
      "location": "US-SC",
      "impact": 37,
      "created_at": "2019-12-04T21:28:26+00:00",
      "updated_at": "2019-12-04T22:33:03+00:00"
    },
    {
      "id": 2,
      "action_id": 1,
      "school_id": "4802532",
      "location": "US-NJ",
      "impact": 43,
      "created_at": "2019-12-04T22:05:29+00:00",
      "updated_at": "2019-12-04T22:05:29+00:00"
    }
  ],
  "meta": {
    "pagination": {
      "total": 2,
      "count": 2,
      "per_page": 20,
      "current_page": 1,
      "total_pages": 1,
      "links": [

      ]
    }
  }
```
