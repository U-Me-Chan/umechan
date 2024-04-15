<?php

namespace Ridouchire\RadioScheduler\RotationStrategies;

use Monolog\Logger;
use Ridouchire\RadioScheduler\GenreSchemas\Day;
use Ridouchire\RadioScheduler\GenreSchemas\Evening;
use Ridouchire\RadioScheduler\GenreSchemas\Morning;
use Ridouchire\RadioScheduler\GenreSchemas\Night;
use Ridouchire\RadioScheduler\IRotation;
use Ridouchire\RadioScheduler\Mpd;

class FullGenre implements IRotation
{
    public const NAME = 'FullGenre';

    public function __construct(
        private Mpd $mpd,
        private Logger $log
    ) {
    }

    public function execute(int $timestamp = 0): void
    {
        if ($timestamp == 0) {
            $timestamp = time() + (60 * 60 * 4);
        }

        $hour = date('G', $timestamp);

        switch ($hour) {
            case 0:
            case 1:
            case 2:
            case 3:
            case 4:
            case 5:
                $this->addPlaylist(Night::getRandom());

                break;
            case 6:
            case 7:
            case 8:
                $this->addPlaylist(Morning::getRandom());

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
                $this->addPlaylist(Day::getRandom());

                break;
            case 19:
            case 20:
            case 21:
            case 22:
            case 23:
                $this->addPlaylist(Evening::getRandom());

                break;

            default:
                throw new \RuntimeException("Неизвестный час: {$hour}");

                break;
        }

    }

    private function addPlaylist(string $playlist): void
    {
        $this->mpd->cropQueue();
        $this->mpd->addToQueue($playlist);
        $this->log->info(self::NAME . ': Ставлю ' . $playlist);
    }
}
