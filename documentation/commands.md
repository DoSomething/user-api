# Commands

This page starts on documentation for custom [Artisan commands](https://laravel.com/docs/6.x/artisan#writing-commands) that have been added to Northstar.

## Update User Fields

Updates a set of users with given field values from a CSV.

The CSV requires a `northstar_id` column, as well as columns for each field to update.

Example:

```
cat ../northstar-updates.csv | php artisan northstar:update --field=email_subscription_status --field=sms_status --verbose
```

In this example, we run the console command with a `northstar-updates.csv` file, which we'd expect to contain the following fields:

* `northstar_id` (required)
* `email_subscription_status`
* `sms_status`
