<?php

namespace App\Types;

use MyCLabs\Enum\Enum;
use Illuminate\Support\Arr;

abstract class Type extends Enum
{
    /**
     * Return all string values.
     *
     * @return array
     */
    public static function all()
    {
        return array_values(static::toArray());
    }

    /**
     * If a type has labels, return the label for the given type.
     *
     * @return string
     */
    public static function label($type)
    {
        if (!method_exists(static::class, 'labels')) {
            throw new \InvalidArgumentException(
                'This type does not have labels.',
            );
        }

        return Arr::get(static::labels(), $type);
    }


    /**
     * Return the given string value.
     *
     * @return string
     */
    public static function get($type)
    {
        return (static::toArray())[$type];
    }
}