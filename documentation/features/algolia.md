# Algolia - Laravel Scout

## Overview

We import & index our Campaigns database to [Algolia](https://www.algolia.com/) using a [Laravel Scout](https://laravel.com/docs/7.x/scout) driver to allow for customized & powerful searching and filtering via our[GraphQL](https://github.com/DoSomething/graphql) service.

_See [this Algolia documentation](https://www.algolia.com/doc/guides/getting-started/how-algolia-works/) for an overview of how Algolia works._

## Campaign Index

Our `Campaign` model contains a [`toSearchableArray` method](https://github.com/DoSomething/rogue/blob/08892e5defd0820edff81e7af65ece686a5de403/app/Models/Campaign.php#L335-L388) which defines & organizes the data to be indexed by Algolia (via Laravel Scout's `Searchable` trait included in the `Campaign` model).

We specifically define the attributes to be indexed, since [we're limited](https://www.algolia.com/doc/faq/basics/is-there-a-size-limit-for-my-index-records/#:~:text=Algolia%20limits%20the%20size%20of,KB%20for%20any%20individual%20record) to 10kb per entry.

Each Campaign is indexed together with its associated Actions which we use to query & filter specific sorts of campaigns. ([This is limited](https://github.com/DoSomething/rogue/pull/1149) to the first 20 Actions due to the size constraints).

## Algolia Transformed Data

Certain attributes are computed or transformed to be compatible with Algolia:

- Dates (typically stored as DateTime values) are converted to [UNIX timestamps](https://www.algolia.com/doc/guides/sending-and-managing-data/prepare-your-data/in-depth/what-is-in-a-record/#dates).
- Attributes to be filtered by `null` or 'missing' are computed as new boolean attributes. (E.g. `is_evergreen` is a computed boolean attribute determined based on whether the Campaign's `end_date` is defined).

## Algolia Indices

Our organization's [Algolia Space](https://www.algolia.com/apps/P5JW2OEUP0/explorer/indices) contains Indices for each of our application environments:

- `local_campaigns`
- `dev_campaigns`
- `qa_campaigns`
- `production_campaigns`

To run the process of indexing the campaigns to Algolia (batch import), the following ENV variables must be assigned:

```
ALGOLIA_APP_ID= #grab the general Application ID
ALGOLIA_SECRET= #grab the API key for the specific env
SCOUT_DRIVER=algolia #this should always be assigned to Algolia since that's what we're using!
```

The Application ID can be found in the [API Keys section](https://www.algolia.com/apps/P5JW2OEUP0/api-keys/all) in Algolia.

The environment specific secret can be found in the list of [All API Keys](https://www.algolia.com/apps/P5JW2OEUP0/api-keys/restricted).

To intiate the batch import on a specific environment, run the following command on the server assigning the specific Heroku application environment via the `-a` flag, e.g.:
`heroku run php artisan scout:import "App\\\Models\\\Campaign" -a dosomething-rogue-qa`

(You can omit the Heroku portion on your local environment of course).

The index name prefix (the environment specific portion prefixing `_campaigns`) to where the batch will import to is defined in the [`scout.php` config](https://github.com/DoSomething/rogue/blob/08892e5defd0820edff81e7af65ece686a5de403/config/scout.php#L19-L30) reading from the `APP_ENV` config variable, e.g. `local`.

### When to run the import

The import should be run across the environments when a new attribute is added, or an attribute is changed so that it's available for indexing and searching right away. (See [Algolia Docs](https://www.algolia.com/doc/guides/sending-and-managing-data/send-and-update-your-data/#updating-your-data)).

## Custom Algolia Settings - Facets & Replicas

We've configured some custom settings via the Algolia UI across our Indices:

### Facet on `causes`

Since we use `causes` on the website for filtering and 'categorizing' camapaigns, we configured a [Facet](https://www.algolia.com/doc/guides/managing-results/refine-results/faceting/) for the `causes` attribute which allows us to filter campaigns by a list of causes.

### Facet on `actions` - `action_type`

Since we use `action_type` on the website for filtering and 'categorizing' campaigns, we configured a [Facet](https://www.algolia.com/doc/guides/managing-results/refine-results/faceting/) for the `actions.action_type` attribute which allows us to filter campaigns by a list of action types.

### Facets on `actions` - `location`

Since we use `location` on the website for filtering and 'categorizing' campaigns, we configured a [Facet](https://www.algolia.com/doc/guides/managing-results/refine-results/faceting/) for the `actions.online` attribute which allows us to filter campaigns by whether a campaign has an online or offline action.

### Facets on `actions` - `time_commitment`

Since we use `time_commitment` on the website for filtering and 'categorizing' campaigns, we configured a [Facet](https://www.algolia.com/doc/guides/managing-results/refine-results/faceting/) for the `actions.time_commitment` attribute which allows us to filter campaigns by a list of time commitment lengths.

### Replica for `start_date`

We often sort campaigns by the `start_date` field (in descending order, to rank by latest campaigns). Algolia requires creating a [Replica Index](https://www.algolia.com/doc/guides/managing-results/refine-results/sorting/in-depth/replicas/) with the configured sorting strategy in order to do so.

We've created a replica across each environments Index with the `start_date_desc` suffix, e.g. `production_campaigns_start_date_desc`. (Following the Algolia [recommended naming conventions](https://www.algolia.com/doc/guides/managing-results/refine-results/sorting/how-to/sort-an-index-by-date/#creating-a-replica)).

**Any configuration updates to an Index's configuration can (and should!) be copied over to the replica as well by selecting that option when the change is made via the Algolia UI**.
