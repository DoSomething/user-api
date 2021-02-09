# Customer.io

We integrate with Customer.io to send transactional and promotional messaging to members.

## Track API

We use the [Track API](https://customer.io/docs/api/#tag/trackOverview) to track member activity in Customer.io, and send both promotional and transactional emails to subscribers.

## App API

We use the [App API](https://customer.io/docs/api/#tag/appOverview) to send transactional emails for forgot password requests, and password updated events.

We use the [send email](https://customer.io/docs/api/#operation/sendEmail) endpoint to send these emails without maintaining a profile for members who have triggered these events. This is done by executing all send email requests for a specific placeholder Customer.io profile we've set up.
