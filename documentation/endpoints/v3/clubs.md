## Groups

### Index

```
GET /api/v3/clubs
```

### Optional Query Parameters

- **filter[id]** _(integer)_

  - Filter results by Club ID or ID's.

- **filter[name]** _(string)_

  - Filter results by names that include the given filter, e.g. `filter[name]=Oakland`.

Example Response:

```
{
  "data": [
    {
      "id": 1,
      "name": "DoSomething Staffers Club",
      "city": "NYC",
      "location": "US-NY",
      "school_id": null,
      "created_at": "2019-12-04T21:28:26+00:00",
      "updated_at": "2019-12-04T22:33:03+00:00"
    },
    {
      "id": 1,
      "name": "Oakland High School Club",
      "city": "Oakland",
      "location": "US-CA",
      "school_id": "3600737",
      "created_at": "2019-12-04T21:28:26+00:00",
      "updated_at": "2019-12-04T22:33:03+00:00"
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

### Show

```
GET /api/v3/clubs/:club_id
```

Example Response:

```
{
    {
      "id": 1,
      "name": "DoSomething Staffers Club",
      "city": "NYC",
      "location": "US-NY",
      "school_id": null,
      "created_at": "2019-12-04T21:28:26+00:00",
      "updated_at": "2019-12-04T22:33:03+00:00"
    },
}
```
