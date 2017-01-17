# Northstar [![Wercker](https://img.shields.io/wercker/ci/548f17b907fa3ea41500a0ec.svg?style=flat-square)](https://app.wercker.com/#applications/548f17b907fa3ea41500a0ec) [![StyleCI](https://styleci.io/repos/26884886/shield)](https://styleci.io/repos/26884886)

This is __Northstar__, the DoSomething.org user & identity service. It's our single "source of truth" for member information.
Northstar is built using [Laravel 5.2](http://laravel.com/docs/5.2) and [MongoDB](https://www.mongodb.com).

### Getting Started

Check out the [API Documentation](https://github.com/DoSomething/northstar/blob/dev/documentation/README.md) to start using
Northstar! :sparkles:

### Contributing

Fork and clone this repository, add to your local [DS Homestead](https://github.com/DoSomething/ds-homestead), and run set-up:

```sh
# Install dependencies:
$ composer install && npm install
    
# Copy the default environment variables:
$ cp .env.example .env

# Make public & private RSA keys:
$ openssl genrsa -out storage/keys/private.key 1024
$ openssl rsa -in storage/keys/private.key -pubout -out storage/keys/public.key

# Run database migrations:
$ php artisan migrate

# And finally, build the frontend assets:
$ npm run build
```

You can seed the database with test data:

    $ php artisan db:seed

You may run unit tests locally using PHPUnit:

    $ vendor/bin/phpunit
    
We follow [Laravel's code style](http://laravel.com/docs/5.1/contributions#coding-style) and automatically
lint all pull requests with [StyleCI](https://styleci.io/repos/26884886). Be sure to configure
[EditorConfig](http://editorconfig.org) to ensure you have proper indentation settings.

Consider [writing a test case](http://laravel.com/docs/5.1/testing) when adding or changing a feature.
Most steps you would take when manually testing your code can be automated, which makes it easier for
yourself & others to review your code and ensures we don't accidentally break something later on!

### Security Vulnerabilities
We take security very seriously. Any vulnerabilities in Northstar should be reported to [security@dosomething.org](mailto:security@dosomething.org),
and will be promptly addressed. Thank you for taking the time to responsibly disclose any issues you find.

### License
&copy;2016 DoSomething.org. Northstar is free software, and may be redistributed under the terms specified
in the [LICENSE](https://github.com/DoSomething/northstar/blob/dev/LICENSE) file. The name and logo for
DoSomething.org are trademarks of Do Something, Inc and may not be used without permission.
