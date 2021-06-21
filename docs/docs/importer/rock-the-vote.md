# Rock The Vote

The importer application integrates with the Rock The Vote (RTV) API to generate a CSV of records to download on an hourly basis.

The QA URL is: https://staging.rocky.rockthevote.com/registrants/new?partner=26299

The Production URL is: https://register.rockthevote.com/registrants/new?partner=37187

For each record that is processed, the importer upserts a `voter-reg` type **post** with a unique `Started registration` datetime

As records are processed, each record with a unique `Started registration` datetime will trigger the importer to create a `voter-reg` type **post** for that user record, and assigning the post to a predefined `action_id` set via a configuration variable.

The import `action_id` is changed each election year, in order to track user registrations per election.

As an additional disclaimer, if a user registers to vote twice in a year (e.g. due to a change of address), two `voter-reg` posts will be created for that user in the specified election year.
