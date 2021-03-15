# Northstar

This is **Northstar**, the DoSomething.org user & identity service. It's our single "source of truth" for member information.
Northstar is built using [Laravel 6.0](https://laravel.com/docs/6.x), [OAuth 2.0 Server](https://oauth2.thephpleague.com), and [MongoDB](https://www.mongodb.com).

### Getting Started

Check out the [API Documentation](https://github.com/DoSomething/northstar/blob/master/documentation/README.md) to start using
Northstar! :sparkles:

### Migrations

Northstar currently uses two database connections: its original MongoDB connection, and the MySQL connection originally owned by [Rogue](https://github.com/DoSomething/rogue).

To run a database migration, it's easiest to use our custom Artisan command:
```
php artisan migrate:all
```

### Contributing

Fork and clone this repository, and [add it to your Homestead](https://github.com/DoSomething/communal-docs/blob/master/Homestead/readme.md).

```sh
# Install dependencies:
$ composer install && npm install

# Configure application & run migrations:
$ php artisan northstar:setup

# And finally, build the frontend assets:
$ npm run build
```

**Pro Tip**: You can run `npm start` after running `npm run build` to initiate a 'watched' webpack server which will recompile following any saved changes to the front end, but be sure to try and re-run `npm run build` if you encounter any styling borkiness, since our [`modernizr` step](https://github.com/DoSomething/northstar/blob/7fddfda34ff09aac31adad1219c4f3300abe378d/package.json#L9) is skipped when running `npm start`.

We follow [Laravel's code style](http://laravel.com/docs/5.5/contributions#coding-style) and automatically
lint all pull requests with [StyleCI](https://styleci.io/repos/26884886). Be sure to configure
[EditorConfig](http://editorconfig.org) to ensure you have proper indentation settings.

### Testing

Performance & debug information is available at [`/__clockwork`](http://northstar.test/__clockwork), or using the [Chrome Extension](https://chrome.google.com/webstore/detail/clockwork/dmggabnehkmmfmdffgajcflpdjlnoemp).

You can seed the database with test data:

    $ php artisan db:seed

You may run unit tests locally using PHPUnit:

    $ phpunit

Consider [writing a test case](http://laravel.com/docs/5.5/testing) when adding or changing a feature.
Most steps you would take when manually testing your code can be automated, which makes it easier for
yourself & others to review your code and ensures we don't accidentally break something later on!

### Security Vulnerabilities

We take security very seriously. Any vulnerabilities in Northstar should be reported to [security@dosomething.org](mailto:security@dosomething.org),
and will be promptly addressed. Thank you for taking the time to responsibly disclose any issues you find.

### License

&copy;2019 DoSomething.org. Northstar is free software, and may be redistributed under the terms specified
in the [LICENSE](https://github.com/DoSomething/northstar/blob/dev/LICENSE) file. The name and logo for
DoSomething.org are trademarks of Do Something, Inc and may not be used without permission.
