<?php

namespace Ridouchire\RadioScheduler\GenreSchemas\HolydayRu;

use Ridouchire\RadioScheduler\IGenreSchema;

class Morning implements IGenreSchema
{
    public static function getAll(): array
    {
        return [
            'Alternative Ru',
            'Pop Ru Chill'
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
                'Pop Ru Chill',
                'Alternative Ru'
            ]
        ];
    }
}
