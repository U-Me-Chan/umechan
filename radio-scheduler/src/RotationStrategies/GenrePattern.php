<?php

namespace Ridouchire\RadioScheduler\RotationStrategies;

use Monolog\Logger;
use Ridouchire\RadioScheduler\IRotation;
use Ridouchire\RadioScheduler\Mpd;

class GenrePattern implements IRotation
{
    public const NAME = 'GenrePattern';

    public function __construct(
        private Mpd $mpd,
        private Logger $log
    ) {
    }

    public function execute(): void
    {
        if ($this->mpd->getQueueCount() > 1) {
            $this->log->debug('GenrePatternStrategy: очередь ещё не подошла к концу');

            return;
        }

        /** @var int */
        $key = array_rand(array_keys($this->getSchema()), 1);

        /** @var array */
        $pls_list = $this->getSchema()[$key];

        shuffle($pls_list);

        $this->log->info('GenrePatternStrategy: ставлю ' . implode(',', $pls_list));

        $track_paths = array_map(function (string $pls) {
            $count = $this->mpd->getCountSongsInDirectory($pls);

            $start = random_int(0, $count);
            $end   = $start + 1;

            if ($start == $count) {
                $end = $count;
                $start = $count - 1;
            }

            /** @var array */
            $_tracks = $this->mpd->getTracks($pls, $start, $end);

            if (empty($_tracks)) {
                throw new \RuntimeException("GenrePatternStrategy: ошибка при получении данных трека для плейлиста {$pls}");
            }

            /** @var array */
            $track = reset($_tracks);

            return $track['file'];
        }, $pls_list);

        $this->mpd->cropQueue();

        array_walk($track_paths, function (string $path) {
            $this->log->info("GenrePatternStrategy: ставлю в очередь файл {$path}");

            $this->mpd->addToQueue($path);
        });
    }

    private function getSchema(): array
    {
        return [
            [
                'Alternative',
                'Alternative High',
                'Alternative Rock',
                'Ru Angst',
                'Japan Rock'
            ],
            [
                'Pop',
                'Korean Pop',
                'Pop Ru',
                'Pop Retro'
            ],
            [
                'Korean Pop',
                'Japan Pop',
                'Japan Rock'
            ],
            [
                'Alternative',
                'Instrumental',
                'Jazz',
                'Video Game Music'
            ],
            [
                'House',
                'Pop Dance Evening',
                'Retrowave',
                'CityPop',
                'Digital Resistance'
            ],
            [
                'DnB Atmosphere',
                'DnB Luquid',
                'Breakcore and Lolicore',
                'Digital Resistance'
            ],
            [
                'Pop Chill Electronica',
                'Chill Electronica',
                'Chill Hop',
                'Slowave'
            ],
            [
                'Pop Ru',
                'Ru Angst'
            ]
        ];
    }
}
