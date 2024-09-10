<?php

namespace Ridouchire\RadioScheduler\GenreSchemas;

use Ridouchire\RadioScheduler\IGenreSchema;

class Morning implements IGenreSchema
{
    public static function getAll(): array
    {
        return [
            'Alternative',
            'Chill Hop',
            'Instrumental',
            'Jazz',
            'Pop Chill Electronica'
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
                'Alternative',
                'Instrumental',
                'Jazz',
                'Alternative Ru'
            ],
            [
                'Pop Chill Electronica',
                'Chill Hop',
                'Pop Ru Chill'
            ]
        ];
    }
}
