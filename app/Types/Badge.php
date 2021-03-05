<?php

namespace App\Types;

class CauseInterestType extends Type
{
    private const ANIMAL_WELFARE = 'animal_welfare';
    private const BULLYING = 'bullying';
    private const EDUCATION = 'education';
    private const ENVIRONMENT = 'environment';
    private const GENDER_RIGHTS_EQUALITY = 'gender_rights_equality';
    private const HOMELESSNESS_POVERTY = 'homelessness_poverty';
    private const IMMIGRATION_REFUGEES = 'immigration_refugees';
    private const LGBTQ_RIGHTS_EQUALITY = 'lgbtq_rights_equality';
    private const MENTAL_HEALTH = 'mental_health';
    private const PHYSICAL_HEALTH = 'physical_health';
    private const RACIAL_JUSTICE_EQUITY = 'racial_justice_equity';
    private const SEXUAL_HARASSMENT_ASSAULT = 'sexual_harassment_assault';

    /**
     * Returns labeled list of values.
     *
     * @return array
     */
    public static function labels()
    {
        return [
            'animal_welfare' => 'Animal Welfare',
            'bullying' => 'Bullying',
            'education' => 'Education',
            'environment' => 'Environment',
            'gender_rights_equality' => 'Gender Rights & Equality',
            'homelessness_poverty' => 'Homelessness & Poverty',
            'immigration_refugees' => 'Immigration & Refugees',
            'lgbtq_rights_equality' => 'LGBTQ+ Rights & Equality',
            'mental_health' => 'Mental Health',
            'physical_health' => 'Physical Health',
            'racial_justice_equity' => 'Racial Justice & Equity',
            'sexual_harassment_assault' => 'Sexual Harassment & Assault',
        ];
    }
}
