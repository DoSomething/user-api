# Mute Promotions

Admin staff can upload a CSV of user IDs that have requested to stop receiving DoSomething related promotional messaging.

As each record in the CSV is processed and imported into the system, it will set the user's `promotions_muted_at` property to the current date and this triggers an event to delete the user's corresponding Customer.io profiles, which is the service we use for promotional messaging.
