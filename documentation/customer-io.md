# Customer.io

We integrate with Customer.io to send transactional and promotional messaging to DoSomething members.

Historically, we've maintained a Customer.io profile for every DoSomething member (a.k.a. Northstar user). We're [currently working on changing this](https://www.pivotaltracker.com/epic/show/4721712) so that we only maintain profiles for active members who are subscribed to receive email and/or SMS promotional messaging.

## Track API

We use the [Track API](https://customer.io/docs/api/#tag/trackOverview) to track member activity in Customer.io, and send both promotional and transactional emails to subscribers. The documentation [here](http://docs.dosomething.org/customer-io) and [here](http://docs.dosomething.org/non-traditional-member-activation) provide more detail, and will soon be moved into this repo.

## App API

We use the [App API](https://customer.io/docs/api/#tag/appOverview) to send transactional emails for forgot password requests, and password updated events.

We use the [send email](https://customer.io/docs/api/#operation/sendEmail) endpoint to send emails to members without requiring a Customer.io profile to exist. This is done by executing all send email requests for a specific ID of a placeholder Customer.io profile we've set up, set via the `CUSTOMER_IO_APP_IDENTIFIER_ID` config variable.
