<?php

namespace Ridouchire\RadioScheduler\GenreSchemas;

use Ridouchire\RadioScheduler\IGenreSchema;

class Day implements IGenreSchema
{
    public static function getAll(): array
    {
        return [
            'Alternative High',
            'Alternative Rock',
            'Pop',
            'Pop Ru',
            'Pop Dance',
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
                'Alternative High',
                'Alternative Ru High',
                'Alternative Rock',
                'Alternative Ru Rock'
            ],
            [
                'Pop',
                'Pop Ru',
            ],
            [
                'Pop Dance',
                'Pop Ru Dance'
            ],
        ];
    }
}
