## Campaigns

## Retrieve All Campaigns (created in Rogue)

```
GET /api/v3/campaigns
```

### Optional Query Parameters

- **filter[column]** _(string)_
  - Filter results by the given column: `id`, `is_open`, `has_website`, `causes`
  - You can filter by more than one value for the ID column, e.g. `/campaigns?filter[id]=121,122`
  - Set the `has_website` filter to `true` (`filter[has_website]=true`) to yield campaign with their `contentful_campaign_id` field populated. Filter for campaigns _without_ the field set with `filter[has_website]=false`.
  - The `has_cause` filter (`/campaigns?filter[causes]=education`) will return campaigns _including_ the specified causes (you can filter by more then one value e.g. `filter[causes]=education,healthcare`).

Example Response:

```
{
    "data": [
        {
            "id": 9000,
            "internal_title": "Test campaign",
            "start_date": "2001-03-13T00:00:00+00:00",
            "end_date": "2001-06-13T00:00:00+00:00",
            "is_open": false,
            "created_at": "2018-12-05T16:24:05+00:00",
            "updated_at": "2018-12-05T16:24:05+00:00"
        },
        {
            "campaign_id": 9001,
            "internal_title": "2nd test campaign",
            "start_date": "2001-03-13T00:00:00+00:00",
            "end_date": "2001-06-13T00:00:00+00:00",
            "is_open": false,
            "created_at": "2018-12-05T16:24:05+00:00",
            "updated_at": "2018-12-05T16:24:05+00:00"
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

## Retrieve A Specific Campaign

```
GET /api/v3/campaigns/:campaign_id
```

Example Response:

```
{
    "data": {
        "id": 9000,
        "internal_title": "Test Campaign",
        "start_date": "2001-03-13T00:00:00+00:00",
        "end_date": "2001-06-13T00:00:00+00:00",
        "is_open": false,
        "created_at": "2018-12-05T16:24:05+00:00",
        "updated_at": "2018-12-05T16:24:05+00:00"
    }
}
```

## Update A Specific Campaign

```
PUT /api/v3/campaigns/:campaign_id
```

- **contentful_campaign_id**: (string)
  The campaign id from contentful where this campaign is being used.

Example request body:

```
{
  "contentful_campaign_id": "123456"
}
```

Example Response:

```
{
    "data": {
        "id": 9000,
        "contentful_campaign_id": "123456",
        "internal_title": "Test Campaign",
        "start_date": "2001-03-13T00:00:00+00:00",
        "end_date": "2001-06-13T00:00:00+00:00",
        "is_open": false,
        "created_at": "2018-12-05T16:24:05+00:00",
        "updated_at": "2018-12-05T16:24:05+00:00"
    }
}
```
