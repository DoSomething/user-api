<?php

namespace App\Providers;

use App\Auth\Scope;
use Illuminate\Support\ServiceProvider;
use libphonenumber\PhoneNumberUtil;

class ValidationServiceProvider extends ServiceProvider
{
    /**
     * The validator instance.
     *
     * @var \Illuminate\Validation\Factory
     */
    protected $validator;

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->validator = $this->app->make('validator');

        // Add custom validator for US mobile numbers.
        // @see: Phoenix's dosomething_user_valid_mobile() function.
        $this->validator->extend(
            'mobile',
            function ($attribute, $value, $parameters) {
                $parser = PhoneNumberUtil::getInstance();

                try {
                    // Make sure that libphonenumber can parse this phone.
                    // @TODO: Consider testing stricter validity here.
                    $parser->parse($value, 'US');

                    // And sanity-check the format is okay:
                    preg_match(
                        '#^(?:\+?([0-9]{1,3})([\-\s\.]{1})?)?\(?([0-9]{3})\)?(?:[\-\s\.]{1})?([0-9]{3})(?:[\-\s\.]{1})?([0-9]{4})#',
                        preg_replace('#[\-\s\.]#', '', $value),
                        $valid,
                    );
                    preg_match(
                        '#([0-9]{1})\1{9,}#',
                        preg_replace('#[^0-9]+#', '', $value),
                        $repeat,
                    );

                    return !empty($valid) && empty($repeat);
                } catch (\libphonenumber\NumberParseException $e) {
                    return false;
                }
            },
            'The :attribute must be a valid US phone number.',
        );

        // Add custom validator for OAuth scopes.
        $this->validator->extend(
            'scope',
            function ($attribute, $value, $parameters) {
                return Scope::validateScopes($value);
            },
            'Invalid scope(s) provided.',
        );

        // Add custom validator for country codes.
        $this->validator->extend(
            'country',
            function ($attribute, $value, $parameters) {
                return get_countries()->has(strtoupper($value));
            },
            'The :attribute must be a valid ISO-3166 country code.',
        );
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
