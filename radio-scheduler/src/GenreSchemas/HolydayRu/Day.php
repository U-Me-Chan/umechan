<?php

namespace Ridouchire\RadioScheduler\GenreSchemas\HolydayRu;

use Ridouchire\RadioScheduler\IGenreSchema;

class Day implements IGenreSchema
{
    public static function getAll(): array
    {
        return [
            'Ru Angst/80',
            'Ru Angst/90',
            'Ru Angst/00',
            'Pop Ru Retro/70',
            'Pop Ru Retro/80',
            'Pop Ru Retro/90',
            'Pop Ru Retro/00',
            'Pop Ru Dance/00'
        ];
    }

    public static function getRandom(): string
    {
        $key = random_int(0, sizeof(self::getAll()) - 1);

        return self::getAll()[$key];
    }

    public static  function getRandomPattern(): array
    {
        return self::getPatterns()[array_rand(self::getPatterns())];
    }

    public static function getPatterns(): array
    {
        return [
            [
                'Ru Angst/00',
                'Ru Angst/90',
            ],
            [
                'Ru Angst/80',
                'Pop Ru Retro/70',
                'Pop Ru Retro/80'
            ],
            [
                'Pop Ru Retro/90',
                'Pop Ru Dance/00'
            ],
        ];
    }
}
