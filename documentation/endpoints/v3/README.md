## v3

These endpoints use OAuth 2 to authenticate. [More information here](https://github.com/DoSomething/northstar/blob/master/documentation/authentication.md)

### Actions

| Endpoint                         | Functionality                                          |
| -------------------------------- | ------------------------------------------------------ |
| `GET /api/v3/actions`            | [Get actions](actions.md#retrieve-all-actions)         |
| `GET /api/v3/actions/:action_id` | [Get an action](actions.md#retrieve-a-specific-action) |

### Action Stats

| Endpoint                   | Functionality                       |
| -------------------------- | ----------------------------------- |
| `GET /api/v3/action-stats` | [Get action stats](action-stats.md) |

### Campaigns

| Endpoint                             | Functionality                                                         |
| ------------------------------------ | --------------------------------------------------------------------- |
| `GET /api/v3/campaigns`              | [Get campaigns](campaigns.md#retrieve-all-campaigns)                  |
| `GET /api/v3/campaigns/:campaign_id` | [Get a campaign](campaigns.md#retrieve-a-specific-campaign)           |
| `PUT /api/v3/campaigns/:campaign_id` | [Update A Specific Campaign](campaigns.md#update-a-specific-campaign) |

### Clubs

| Endpoint                     | Functionality               |
| ---------------------------- | --------------------------- |
| `GET /api/v3/clubs`          | [Get clubs](clubs.md#index) |
| `GET /api/v3/clubs/:club_id` | [Get a club](clubs.md#show) |

### Groups

| Endpoint                       | Functionality                 |
| ------------------------------ | ----------------------------- |
| `GET /api/v3/groups`           | [Get groups](groups.md#index) |
| `GET /api/v3/groups/:group_id` | [Get a group](groups.md#show) |

### Group Types

| Endpoint                                 | Functionality                           |
| ---------------------------------------- | --------------------------------------- |
| `GET /api/v3/group-types`                | [Get group types](group-types.md#index) |
| `GET /api/v3/group-types/:group_type_id` | [Get a group type](group-types.md#show) |

### Posts

| Endpoint                        | Functionality                                   |
| ------------------------------- | ----------------------------------------------- |
| `POST /api/v3/posts`            | [Create a post](posts.md#create-a-post)         |
| `GET /api/v3/posts`             | [Get posts](posts.md#retrieve-all-posts)        |
| `GET /api/v3/posts/:post_id`    | [Get a post](posts.md#retrieve-a-specific-post) |
| `DELETE /api/v3/posts/:post_id` | [Delete a post](posts.md#delete-a-post)         |
| `PATCH /api/v3/posts/:post_id`  | [Update a post](posts.md#update-a-post)         |

### Questionnaires

| Endpoint                      | Functionality                                                                                        |
| ----------------------------- | ---------------------------------------------------------------------------------------------------- |
| `POST /api/v3/questionnaires` | [Store a user's Questionnaire submission](questionnaires.md#store-a-user's-questionnaire-submission) |

### Reactions

| Endpoint                                | Functionality                                                                |
| --------------------------------------- | ---------------------------------------------------------------------------- |
| `POST /api/v3/posts/:post_id/reactions` | [Create or update a Reaction](reactions.md#create-or-update-a-reaction)      |
| `GET /api/v3/posts/:post_id/reactions`  | [Get all reactions of a post](reactions.md#Retrieve-all-reactions-of-a-post) |

### Tags

| Endpoint                           | Functionality                                      |
| ---------------------------------- | -------------------------------------------------- |
| `POST /api/v3/posts/:post_id/tags` | [Tag or Untag a Post](tags.md#tag-or-untag-a-post) |

### Reviews

| Endpoint                              | Functionality                                                       |
| ------------------------------------- | ------------------------------------------------------------------- |
| `POST /api/v3/posts/:post_id/reviews` | [Create or update a Review](reviews.md#create-or-update-a-reaction) |

### Rotate

| Endpoint                             | Functionality       |
| ------------------------------------ | ------------------- |
| `POST /api/v3/posts/:post_id/rotate` | Rotate a post image |

### Signups

| Endpoint                            | Functionality                                         |
| ----------------------------------- | ----------------------------------------------------- |
| `POST /api/v3/signups`              | [Create a signup](signups.md#create-a-signup)         |
| `GET /api/v3/signups`               | [Get signups](signups.md#retrieve-all-signups)        |
| `GET /api/v3/signups/:signup_id`    | [Get a signup](signups.md#retrieve-a-specific-signup) |
| `PATCH /api/v3/signups/:signup_id`  | [Update a signup](signups.md#update-a-signup)         |
| `DELETE /api/v3/signups/:signup_id` | [Delete a signup](signups.md#delete-a-signup)         |
