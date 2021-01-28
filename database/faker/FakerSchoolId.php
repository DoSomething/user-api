<?php

use Faker\Generator;
use Faker\Provider\Base;
use League\Csv\Reader;

class FakerSchoolId extends Base
{
    /**
     * A list of schools from our Schools DB.
     *
     * @var array
     */
    protected $schools = [];

    public function __construct(Generator $generator)
    {
        parent::__construct($generator);

        // We use real school ID's so GraphQL queries that join on Schools don't break.
        $reader = Reader::createFromPath('database/faker/schools.csv', 'r');

        $reader->setHeaderOffset(0);

        foreach ($reader->getRecords() as $school) {
            array_push($this->schools, (object) $school);
        }
    }

    /**
     * Return a random School.
     *
     * @return object
     */
    public function school()
    {
        return $this->schools[array_rand($this->schools)];
    }

    /**
     * Return a random School ID.
     *
     * @return string
     */
    public function school_id()
    {
        return $this->school()->school_id;
    }
}
