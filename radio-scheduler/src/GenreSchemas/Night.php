<?php

namespace Ridouchire\RadioScheduler\GenreSchemas;

use Ridouchire\RadioScheduler\IGenreSchema;

class Night implements IGenreSchema
{
    public static function getAll(): array
    {
        return [
            'Chill Electronica',
            'Chill Hop',
            'DnB Atmosphere',
            'DnB Liquid',
            'House'
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
                'Chill Electronica',
                'Chill Hop'
            ],
            [
                'DnB Liquid',
                'DnB Atmosphere'
            ]
        ];
    }
}
