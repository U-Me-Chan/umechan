<?php

namespace Ridouchire\RadioScheduler\Utils;

class TickCounter
{
    private static int $count;

    public static function getCount(): int
    {
        /** @phpstan-ignore identical.alwaysFalse */
        if (null === self::$count) {
            self::$count = 0;
        }

        return self::$count;
    }

    public static function create(int $value): void
    {
        self::$count = $value;
    }

    public static function reset(): void
    {
        self::$count = 0;
    }

    public static function tick(): void
    {
        /** @phpstan-ignore identical.alwaysFalse */
        if (null === self::$count) {
            self::$count = 0;
        }

        self::$count = self::$count + 1;
    }

    private function __construct()
    {
    }
}
