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
            'Breakcore and Lolicore',
            'Pop',
            'Pop Dance',
            'Pop Retro',
            'Korean Pop',
            'Japan Pop',
            'Japan Rock',
            'Ru Angst',
            'Pop Ru'
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
                'Alternative Rock',
                'Ru Angst',
                'Japan Rock'
            ],
            [
                'Pop',
                'Pop Ru',
                'Pop Retro'
            ],
            [
                'Korean Pop',
                'Japan Pop'
            ],
            [
                'Pop Dance',
                'Pop Retro Dance',
                'Pop Ru Dance'
            ],
        ];
    }
}
