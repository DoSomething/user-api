<?php

class FakerPhoneNumber extends \Faker\Provider\en_US\PhoneNumber
{
    /**
     * Phone number formats, sans extensions.
     *
     * @see https://git.io/vNEkY
     */
    protected static $formats = [
        // International format
        '+1-{{areaCode}}-{{exchangeCode}}-####',
        '+1 ({{areaCode}}) {{exchangeCode}}-####',
        '+1-{{areaCode}}-{{exchangeCode}}-####',
        '+1.{{areaCode}}.{{exchangeCode}}.####',
        '+1{{areaCode}}{{exchangeCode}}####',

        // Standard formats
        '{{areaCode}}-{{exchangeCode}}-####',
        '({{areaCode}}) {{exchangeCode}}-####',
        '1-{{areaCode}}-{{exchangeCode}}-####',
        '{{areaCode}}.{{exchangeCode}}.####',

        '{{areaCode}}-{{exchangeCode}}-####',
        '({{areaCode}}) {{exchangeCode}}-####',
        '1-{{areaCode}}-{{exchangeCode}}-####',
        '{{areaCode}}.{{exchangeCode}}.####',
    ];
}
