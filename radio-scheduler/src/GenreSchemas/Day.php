<?php

namespace Ridouchire\RadioScheduler\GenreSchemas;

use Ridouchire\RadioScheduler\IGenreSchema;

class Day implements IGenreSchema
{
    public static function getAll(): array
    {
        return [
            'Alternative High/20',
            'Alternative Rock/20',
            'Ru Angst',
            'Pop/20',
            'Pop Ru',
            'Pop Dance/20',
            'Pop Ru Dance',
            'Pop Retro Dance'
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
                'Alternative High/20',
                'Alternative Rock/20',
                'Ru Angst',
            ],
            [
                'Pop/20',
                'Pop Ru',
            ],
            [
                'Pop Dance/20',
                'Pop Retro Dance',
                'Pop Ru Dance'
            ],
        ];
    }
}
