# Customer.io

We integrate with [Customer.io](https://customer.io/) to send transactional and promotional messaging to DoSomething members.

We maintain Customer.io profiles for active members who are subscribed to receive email and/or SMS promotional messaging.

### Email subscribers

When a user's `email_subscription_status` boolean field is set to `true`, they are subscribed to email promotions.

The current `email_subscription_topics` a user can subscribe to are:

* `community`
* `lifestyle`
* `news`
* `scholarships`

### SMS subscribers

The user's `sms_status` field determines their SMS subscription status (a heavy TODO is to rename it as `sms_subscription_status` for consistency with email):

* `active` - receives weekly promotional SMS broadcasts
* `less` - receives monthly promotional SMS broadcasts
* `stop` - has unsubscribed from all SMS messaging
* `pending` - has received a re-permissioning SMS broadcast (a.k.a. an [`askSubscriptionStatusBroadcast`](https://github.com/DoSomething/gambit-admin/wiki/Broadcasts#asksubscriptionstatus)), prompting them to answer with `active`, `less`, or `stop`
* `undeliverable` - we set this internally if Twilio cannot successfully deliver a SMS broadcast to the user's mobile number 

The current `sms_subscription_topics` are `general` and `voting`. By default, users are opted into both topics when they subscribe, unless the user is being imported from Rock The Vote (see [details](https://github.com/DoSomething/chompy/tree/master/docs/imports#sms-subscription)). 

### Subscription updates

* When a new account is created (via web or SMS), the member is subscribed for promotions by default, and a Customer.io profile is created for them.

* If a member unsubscribes from both SMS and email promotions, their `promotions_muted_at` field will be set to the datetime they've unsubscribed. This will trigger their Customer.io profile deletion.

* If a member whose promotions have been muted resubscribes to either email and/or SMS, their `promotions_muted_at` field will be set to null. This will trigger their Customer.io profile to be re-created, and additionally track a `promotions_resubscribe` Customer.io event.

### Mute Promotions import

Admins can run a Mute Promotions import from [Chompy](https://github.com/DoSomething/chompy/tree/master/docs/imports#mute-promotions) to manually set a user's `promotions_muted_at` field and delete their Customer.io profile.

## Integration

Northstar uses [queued jobs](https://laravel.com/docs/6.x/queues) to execute Customer.io API requests.

### Track API

We use the [Track API](https://customer.io/docs/api/#tag/trackOverview) to upsert a profile for each active, subscribed member, and to track their [events](https://customer.io/docs/events). The documentation [here](http://docs.dosomething.org/customer-io) and [here](http://docs.dosomething.org/non-traditional-member-activation) provide more detail, and will soon be moved into this repo.

### App API

We use the [App API](https://customer.io/docs/api/#tag/appOverview) to send transactional emails for forgot password requests, and password updated events.

We use the [send email](https://customer.io/docs/api/#operation/sendEmail) endpoint to send emails to members without requiring a Customer.io profile to exist. This is done by executing all send email requests for a specific ID of a placeholder Customer.io profile we've set up, set via the `CUSTOMER_IO_APP_IDENTIFIER_ID` config variable.

## History

When we first launched our Customer.io integration in 2016, we maintained a Customer.io profile for every DoSomething member (a.k.a. Northstar user). We changed this in 2021 to only maintain profiles for [active members who are subscribed](https://www.pivotaltracker.com/epic/show/4721712), running the first [Mute Promotions import](#mute-promotions-import) at the end of February 2021.
