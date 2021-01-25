<?php

namespace App\Providers;

use App\Auth\Scope;
use Illuminate\Support\ServiceProvider;
use libphonenumber\PhoneNumberUtil;
use Illuminate\Support\Facades\Validator;

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

        // Add a custom validator for Mongo ObjectIDs.
        Validator::extend(
            'objectid',
            function ($attribute, $value, $parameters, $validator) {
                return preg_match('/^[a-f\d]{24}$/i', $value);
            },
            'The :attribute must be a valid ObjectID.',
        );

        Validator::extend(
            'iso3166',
            function ($attribute, $value, $parameters, $validator) {
                $isoCodes = new \Sokil\IsoCodes\IsoCodesFactory();
                $subDivisions = $isoCodes->getSubdivisions();

                return !is_null($subDivisions->getByCode($value));
            },
            'The :attribute must be a valid ISO-3166-2 region code.',
        );

        // Add custom validator for US mobile numbers.
        // @see: Ashes' dosomething_user_valid_mobile() function.
        Validator::extend(
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
        Validator::extend(
            'scope',
            function ($attribute, $value, $parameters) {
                return Scope::validateScopes($value);
            },
            'Invalid scope(s) provided.',
        );

        // Add custom validator for country codes.
        Validator::extend(
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
