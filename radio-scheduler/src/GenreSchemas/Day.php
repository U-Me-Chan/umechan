<?php

namespace Ridouchire\RadioScheduler\GenreSchemas;

use Ridouchire\RadioScheduler\IGenreSchema;

class Day implements IGenreSchema
{
    public static function getAll(): array
    {
        return [
            'Alternative High',
            'Alternative High/20',
            'Alternative Rock',
            'Alternative Rock/20',
            'Pop',
            'Pop/20',
            'Pop Ru',
            'Pop Dance',
            'Pop Dance/20',
            'Pop Ru Dance',
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
                'Alternative Ru High/20',
                'Alternative Rock/20',
                'Alternative Ru Rock/20'
            ],
            [
                'Pop/20',
                'Pop Ru/20',
            ],
            [
                'Pop Dance/20',
                'Pop Ru Dance/20'
            ],
        ];
    }
}
