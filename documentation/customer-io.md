# Customer.io

We integrate with Customer.io to send transactional and promotional messaging to DoSomething members.

We maintain Customer.io profiles for active members who are subscribed to receive email and/or SMS promotional messaging.

## Subscription updates

* If a member unsubscribes from both SMS and email promotions, their `promotions_muted_at` field will be set to the datetime they've unsubscribed. This will trigger their Customer.io profile deletion.

* If a member whose promotions have been muted resubscribes to either email and/or SMS, their `promotions_muted_at` field will be set to null. This will trigger their Customer.io profile to be re-created, and additionally track a `promotions_resubscribe` Customer.io event.


## Integration

### Track API

We use the [Track API](https://customer.io/docs/api/#tag/trackOverview) to track member activity in Customer.io, and send both promotional and transactional emails to subscribers. The documentation [here](http://docs.dosomething.org/customer-io) and [here](http://docs.dosomething.org/non-traditional-member-activation) provide more detail, and will soon be moved into this repo.

### App API

We use the [App API](https://customer.io/docs/api/#tag/appOverview) to send transactional emails for forgot password requests, and password updated events.

We use the [send email](https://customer.io/docs/api/#operation/sendEmail) endpoint to send emails to members without requiring a Customer.io profile to exist. This is done by executing all send email requests for a specific ID of a placeholder Customer.io profile we've set up, set via the `CUSTOMER_IO_APP_IDENTIFIER_ID` config variable.

## History

When we first launched our Customer.io integration in 2016, we maintained a Customer.io profile for every DoSomething member (a.k.a. Northstar user). We changed this in 2021 to only maintain profiles for [active members who are subscribed](https://www.pivotaltracker.com/epic/show/4721712), running the first Mute Promotions import at the end of February 2021.