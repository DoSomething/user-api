<?php

use Faker\Provider\Base;

class FakerNorthstarId extends Base
{
    /**
     * A selection of users from Northstar Dev.
     *
     * @var array
     */
    protected static $ids = [
        '5554eac1a59dbf117e8b4567',
        '5570b6cea59dbf3b7a8b4567',
        '5575e568a59dbf3b7a8b4572',
        '55844355a59dbfa93d8b458d',
        '5589c991a59dbfa93d8b45ae',
        '559442cca59dbfca578b4bf3',
        '559442c4a59dbfc9578b4b76',
        '559442cca59dbfc9578b4bf4',
        '55882c57a59dbfa93d8b4599',
        '55767606a59dbf3c7a8b4571',
        '559442c4a59dbfc9578b4b6a',
        '562af453a59dbffa178b456c',
        '5639066ba59dbfe6598b4567',
        '574ef3b0a59dbfa5768b456a',
        '58e68d5f7f43c23c11117e92',
        '5575e568a59dbf3b7a8b4572',
        '559442a1a59dbfca578b491f',
        '559442a9a59dbfca578b49a9',
        '559442c0a59dbfca578b4b34',
        '559442cca59dbfca578b4bed',
        '559442cca59dbfc9578b4bee',
        '5581896ca59dbfa83d8b4575',
        '5609ae4ea59dbfac7b8b4568',
        '561563f9a59dbfbe378b4567',
        '564f36aca59dbf3e6b8b4cfb',
        '56d5b5eda59dbfff6a8b45a8',
        '56d5baa7a59dbf106b8b45aa',
        '56d9f70da59dbf176b8b45d2',
        '574dace47f43c21f1e0d674c',
        '57ab7628a59dbf5a3d8b4795',
        '57ab7628a59dbf593d8b4797',
        '581ba6dd7f43c26c6d2349d3',
        '5952b74b7f43c2047204168c',
        '596cfab87f43c230283a23d7',
        '596d07457f43c2315d2ac928',
        '596d0f737f43c2315d2ac935',
    ];

    /**
     * Return a random Northstar ID.
     *
     * @return mixed
     */
    public function northstar_id()
    {
        return static::randomElement(static::$ids);
    }
}
