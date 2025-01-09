<?php

namespace Ridouchire\RadioScheduler\GenreSchemas;

use Ridouchire\RadioScheduler\IGenreSchema;

class Evening implements IGenreSchema
{
    public static function getAll(): array
    {
        return [
            'Alternative',
            'CityPop',
            'Digital Resistance',
            'Chill Electronica',
            'Pop Chill Electronica',
            'Retrowave',
            'Slowave',
            'Vaporwave',
            'Video Game Music',
            'DnB Atmosphere',
            'DnB Liquid',
            'Pop Dance Evening',
            'Pop Ru Dance Evening',
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
                'Retrowave',
                'Video Game Music'
            ],
            [
                'CityPop',
                'Slowave',
                'Vaporwave'
            ],
            [
                'Pop Chill Electronica',
                'Chill Electronica',
                'Slowave',
                'Pop Ru Chill'
            ],
                        [
                'DnB Atmosphere',
                'DnB Liquid',
                'Digital Resistance'
            ],
            [
                'Pop Dance Evening',
                'Retrowave',
                'Digital Resistance',
                'Pop Ru Dance Evening'
            ],
        ];
    }
}
