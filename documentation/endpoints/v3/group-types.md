## Group Types

### Index

```
GET /api/v3/group-types
```

Example Response:

```
{
  "data": [
    {
      "id": 1,
      "name": "March For Our Lives",
      "filter_by_location": false,
      "created_at": "2019-12-04T21:28:26+00:00",
      "updated_at": "2019-12-04T22:33:03+00:00"
    },
    {
      "id": 2,
      "name": "College Board",
      "filter_by_location": true,
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
GET /api/v3/group-types/:group_type_id
```

Example Response:

```
{
  "data": {
    "id": 1,
    "name": "March For Our Lives",
    "filter_by_location": false,
    "created_at": "2019-12-04T21:28:26+00:00",
    "updated_at": "2019-12-04T22:33:03+00:00"
  };
}
```
