<?php

namespace App\Providers;

use App\Auth\Scope;
use Illuminate\Support\Facades\Validator;
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
                return is_phone_number($value);
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
