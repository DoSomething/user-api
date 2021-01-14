<?php

namespace App\Types;

class CauseInterests
{
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
