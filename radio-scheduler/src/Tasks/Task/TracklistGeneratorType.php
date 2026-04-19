<?php

namespace Ridouchire\RadioScheduler\Tasks\Task;

use InvalidArgumentException;

enum TracklistGeneratorType
{
    case random;
    case average;
    case best;
    case new_or_long_standing;
    case smart;

    public static function fromString(string $value): self
    {
        return match($value) {
            'random'  => self::random,
            'average' => self::average,
            'best'    => self::best,
            'new'     => self::new_or_long_standing,
            'smart'   => self::smart,
            default   => throw new InvalidArgumentException()
        };
    }
}
