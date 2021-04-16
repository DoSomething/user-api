<?php

namespace App\Types;

class SmsStatus
{
    /**
     * @var string
     */
    public static $active = 'active';

    /**
     * @var string
     */
    public static $less = 'less';

    /**
     * @var string
     */
    public static $pending = 'pending';

    /**
     * @var string
     */
    public static $stop = 'stop';

    /**
     * @var string
     */
    public static $undeliverable = 'undeliverable';
}
