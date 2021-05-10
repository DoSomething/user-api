## Groups

### Index

```
GET /api/v3/groups
```

### Optional Query Parameters

- **filter[group_type_id]** _(integer)_

  - Filter results by Group Type ID or ID's.

- **filter[id]** _(integer)_

  - Filter results by Group ID or ID's.

- **filter[location]** _(string)_

  - Filter results by given location, e.g. `filter[state]=US-SC`.

- **filter[name]** _(string)_

  - Filter results by names that include the given filter, e.g. `filter[name]=New`.

- **filter[school_id]** _(string)_

  - Filter results by given school_id, e.g. `filter[external_id]=13000419`.

Example Response:

```
{
  "data": [
    {
      "id": 12,
      "group_type_id": 1,
      "name": "A C Flora High School",
      "city": "Charleston",
      "location": "US-SC",
      "school_id": "3600737",
      "goal": 150,
      "created_at": "2019-12-04T21:28:26+00:00",
      "updated_at": "2019-12-04T22:33:03+00:00"
    },
    {
      "id": 23,
      "group_type_id": 1,
      "name": "Afton Central School",
      "city": "Afton",
      "location": "US-NY",
      "school_id": null,
      "goal": null,
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

### Show

```
GET /api/v3/groups/:group_id
```

Example Response:

```
{
  "data": {
    "id": 12,
    "group_type_id": 1,
    "name": "A C Flora High School",
    "city": "Charleston",
    "location": "US-SC",
    "school_id": "3600737",
    "goal": 150,
    "created_at": "2019-12-04T21:28:26+00:00",
    "updated_at": "2019-12-04T22:33:03+00:00"
  };
}
```
