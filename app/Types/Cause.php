<?php

namespace App\Types;

class Cause extends Type
{
    private const ADDICTION = 'addiction';
    private const ANIMAL_WELFARE = 'animal-welfare';
    private const BODY_POSITIVITY = 'body-positivity';
    private const BULLYING = 'bullying';
    private const CRIMINAL_JUSTICE = 'criminal-justice';
    private const DISASTER_RELIEF = 'disaster-relief';
    private const DISCRIMINATION = 'discrimination';
    private const EDUCATION = 'education';
    private const ENVIRONMENT = 'environment';
    private const FINANCIAL_SKILLS = 'financial-skills';
    private const GENDER_RIGHTS = 'gender-rights';
    private const GUN_VIOLENCE = 'gun-violence';
    private const HEALTHCARE = 'healthcare';
    private const HOMELESSNESS_AND_POVERTY = 'homelessness-and-poverty';
    private const IMMIGRATION = 'immigration';
    private const INCOME_INEQUALITY = 'income-inequality';
    private const LITERACY = 'literacy';
    private const LGBTQ_RIGHTS = 'lgbtq-rights';
    private const MENTAL_HEALTH = 'mental-health';
    private const PHYSICAL_HEALTH = 'physical-health';
    private const RACIAL_JUSTICE = 'racial-justice';
    private const RELATIONSHIP = 'relationship';
    private const ROAD_SAFETY = 'road-safety';
    private const SENIORS = 'seniors';
    private const TECHNOLOGY = 'technology';
    private const SEXUAL_HARRASSMENT = 'sexual-harassment';
    private const VETERANS = 'veterans';
    private const VOTER_REGISTRATION = 'voter-registration';
    private const WEEK_OF_ACTION = 'week-of-action';
    private const OTHER = 'other';

    /**
     * Returns labeled list of values.
     *
     * @return array
     */
    public static function labels()
    {
        return [
            self::ADDICTION => 'Addiction',
            self::ANIMAL_WELFARE => 'Animal Welfare',
            self::BODY_POSITIVITY => 'Body Positivity',
            self::BULLYING => 'Bullying',
            self::CRIMINAL_JUSTICE => 'Criminal Justice',
            self::DISASTER_RELIEF => 'Disaster Relief',
            self::DISCRIMINATION => 'Discrimination',
            self::EDUCATION => 'Education (access, affordability)',
            self::ENVIRONMENT => 'Environment',
            self::FINANCIAL_SKILLS => 'Financial Skills',
            self::GENDER_RIGHTS => 'Gender Rights & Equality',
            self::GUN_VIOLENCE => 'Gun Violence',
            self::HEALTHCARE => 'Healthcare',
            self::HOMELESSNESS_AND_POVERTY => 'Homelessness & Poverty',
            self::IMMIGRATION => 'Immigration/Refugees',
            self::INCOME_INEQUALITY => 'Income Inequality',
            self::LITERACY => 'Literacy',
            self::LGBTQ_RIGHTS => 'LGBTQ+ Rights & Equality ',
            self::MENTAL_HEALTH => 'Mental Health',
            self::PHYSICAL_HEALTH => 'Physical Health',
            self::RACIAL_JUSTICE => 'Racial Justice/Racial Equity',
            self::RELATIONSHIP => 'Relationship',
            self::ROAD_SAFETY => 'Road Safety',
            self::SENIORS => 'Seniors',
            self::SEXUAL_HARRASSMENT => 'Sexual Harassment & Assault',
            self::TECHNOLOGY => 'Technology',
            self::VETERANS => 'Military/Veterans',
            self::VOTER_REGISTRATION => 'Voter Registration',
            self::WEEK_OF_ACTION => 'Week of Action',
            self::OTHER => 'Other',
        ];
    }
}
