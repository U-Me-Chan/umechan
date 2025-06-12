<?php

namespace Ridouchire\RadioScheduler\GenreSchemas\HolydayRu;

use Ridouchire\RadioScheduler\IGenreSchema;

class Evening implements IGenreSchema
{
    public static function getAll(): array
    {
        return [
            'Pop Ru Retro/70',
            'Pop Ru Retro/80',
            'Pop Ru Retro/90',
            'Pop Ru Chill'
        ];
    }

    public static function getRandom(): string
    {
        $key = random_int(0, sizeof(self::getAll()) - 1);

        return self::getAll()[$key];
    }

    public static function getRandomPattern(): array
    {
        return self::getPatterns()[array_rand(self::getPatterns())];
    }

    public static function getPatterns(): array
    {
        return [
            [
                'Pop Ru Retro/70',
                'Pop Ru Retro/80',
            ],
            [
                'Pop Ru Retro/90',
                'Pop Ru Chill'
            ]
        ];
    }
}
