<?php

namespace Ridouchire\RadioScheduler\Utils;

class TickCounter
{
    private static $count;

    public static function getCount()
    {
        if (null === self::$count) {
            self::$count = 0;
        }

        return self::$count;
    }

    public static function create(int $value)
    {
        self::$count = $value;
    }

    public static function reset()
    {
        self::$count = 0;
    }

    public static function tick()
    {
        if (null === self::$count) {
            self::$count = 0;
        }

        self::$count = self::$count + 1;
    }

    private function __construct()
    {
    }
}
