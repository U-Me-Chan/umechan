<?php

namespace Ridouchire\RadioScheduler\RotationStrategies;

use Monolog\Logger;
use Ridouchire\RadioScheduler\GenreSchemas\Day;
use Ridouchire\RadioScheduler\GenreSchemas\Evening;
use Ridouchire\RadioScheduler\GenreSchemas\Morning;
use Ridouchire\RadioScheduler\GenreSchemas\Night;
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

    public function execute(int $timestamp = 0): void
    {
        if ($this->mpd->getQueueCount() > 1) {
            $this->log->debug('GenrePatternStrategy: очередь ещё не подошла к концу');

            return;
        }

        if ($timestamp == 0) {
            $timestamp = time() + (60 * 60 * 4);
        }

        $hour = date('G', $timestamp);

        switch($hour) {
            case 0:
            case 1:
            case 2:
            case 3:
            case 4:
            case 5:
                $pls_list = Night::getRandomPattern();

                break;
            case 6:
            case 7:
            case 8:
                $pls_list = Morning::getRandomPattern();

                break;
            case 9:
            case 10:
            case 11:
            case 12:
            case 13:
            case 14:
            case 15:
            case 16:
            case 17:
            case 18:
                $pls_list = Day::getRandomPattern();

                break;
            case 19:
            case 20:
            case 21:
            case 22:
            case 23:
                $pls_list = Evening::getRandomPattern();
                break;
            default:
                throw new \RuntimeException("Неизвестный час: {$hour}");

                break;
        }

        shuffle($pls_list);

        $this->log->info('GenrePatternStrategy: ставлю ' . implode(',', $pls_list));

        $track_paths = [];

        foreach ($pls_list as $pls) {
            $limit = random_int(3, 5);

            for ($i = 0; $i < $limit; $i++) {
                $count = $this->mpd->getCountSongsInDirectory($pls);

                $start = random_int(0, $count);
                $end   = $start + 1;

                if ($start == $count) {
                    $end   = $count;
                    $start = $count - 1;
                }

                /** @var array */
                $_tracks = $this->mpd->getTracks($pls, $start, $end);

                if (empty($_tracks)) {
                    throw new \RuntimeException("GenrePatternStrategy: ошибка при получении данных трека для плейлиста {$pls}");
                }

                /** @var array */
                $track = reset($_tracks);

                $track_paths[] = $track['file'];
            }
        }

        $this->mpd->cropQueue();

        array_walk($track_paths, function (string $path) {
            $this->log->info("GenrePatternStrategy: ставлю в очередь файл {$path}");

            $this->mpd->addToQueue($path);
        });
    }
}
