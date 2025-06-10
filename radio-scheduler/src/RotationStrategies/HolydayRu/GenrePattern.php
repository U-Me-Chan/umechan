<?php

namespace Ridouchire\RadioScheduler\RotationStrategies\HolydayRu;

use Medoo\Medoo;
use Monolog\Logger;
use Ridouchire\RadioScheduler\GenreSchemas\HolydayRu\Day;
use Ridouchire\RadioScheduler\GenreSchemas\HolydayRu\Evening;
use Ridouchire\RadioScheduler\GenreSchemas\HolydayRu\Morning;
use Ridouchire\RadioScheduler\IRotation;
use Ridouchire\RadioScheduler\Mpd;

class GenrePattern implements IRotation
{
    public const NAME = 'GenrePattern';

    public function __construct(
        private Medoo $db,
        private Mpd $mpd,
        private Logger $log
    ) {
    }

    public function execute(int $timestamp = 0): void
    {
        if ($this->mpd->getQueueCount() > 1) {
            $this->log->debug(self::NAME . ': очередь ещё не подошла к концу');

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
                $pls_list = Evening::getRandomPattern();

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
        }

        shuffle($pls_list);

        $this->log->info(self::NAME . ': ставлю ' . implode(',', $pls_list));

        $track_paths = [];

        foreach ($pls_list as $pls) {
            $track_paths = array_merge($track_paths, $this->db->rand('tracks', 'path', [
                'path[~]'      => "{$pls}/%",
                'estimate[>=]' => 0,
                'LIMIT'        => [0, 12]
            ]));
        }

        $this->mpd->cropQueue();

        array_walk($track_paths, function (string $path) {
            $this->log->info(self::NAME . ": ставлю в очередь файл {$path}");

            $this->mpd->addToQueue($path);
        });
    }
}
