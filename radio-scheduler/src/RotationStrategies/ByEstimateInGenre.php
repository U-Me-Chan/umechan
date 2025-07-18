<?php

namespace Ridouchire\RadioScheduler\RotationStrategies;

use Medoo\Medoo;
use Monolog\Logger;
use Ridouchire\RadioScheduler\IRotation;
use Ridouchire\RadioScheduler\Commercials;
use Ridouchire\RadioScheduler\Jingles;
use Ridouchire\RadioScheduler\Mpd;
use Ridouchire\RadioScheduler\GenreSchemas\Day;
use Ridouchire\RadioScheduler\GenreSchemas\Evening;
use Ridouchire\RadioScheduler\GenreSchemas\Morning;
use Ridouchire\RadioScheduler\GenreSchemas\Night;

class ByEstimateInGenre implements IRotation
{
    public const NAME = 'ByEstimateInGenre';

    public function __construct(
        private Medoo $db,
        private Jingles $jingles,
        private Commercials $commercials,
        private Mpd $mpd,
        private Logger $logger
    ) {
    }

    public function execute(int $timestamp = 0): void
    {
        if ($this->mpd->getQueueCount() > 1) {
            $this->logger->debug(self::NAME . ': очередь ещё не подошла к концу');

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
                $genre = Night::getRandom();

                break;
            case 6:
            case 7:
            case 8:
                $genre = Morning::getRandom();

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
                $genre = Day::getRandom();

                break;
            case 19:
            case 20:
            case 21:
            case 22:
            case 23:
                $genre = Evening::getRandom();
                break;
            default:
                throw new \RuntimeException("Неизвестный час: {$hour}");
        }

        $track_paths = [];
        $track_count = random_int(5, 7);

        /** @phpstan-ignore argument.type, arguments.count */
        $avg_track_paths = $this->db->select('tracks', 'path', [
            'path[~]' => "{$genre}/%",
            'estimate[>=]' => Medoo::raw("(SELECT AVG(estimate) FROM tracks WHERE path LIKE '{$genre}/%')"),
            'last_playing[<]' => time() + (60 * 60 * 2),
            'ORDER' => [
                'last_playing' => 'ASC'
            ],
            'LIMIT' => [0, $track_count]
        ]);

        /** @phpstan-ignore argument.type, arguments.count */
        $new_track_path = $this->db->select('tracks', 'path', [
            'path[~]' => "{$genre}/%",
            'ORDER'   => [
                'play_count' => 'ASC',
            ],
            'LIMIT' => [0, $track_count]
        ]);

        /** @phpstan-ignore argument.type, argument.type */
        $track_paths = array_merge($avg_track_paths, $new_track_path);

        $this->logger->debug(implode(',', $track_paths));

        /** @phpstan-ignore argument.unresolvableType */
        shuffle($track_paths);

        list($jingle)                           = $this->jingles->getJingles(1);
        list($comm_one, $comm_two, $comm_three) = $this->commercials->getCommercials();

        array_unshift($track_paths, $comm_three);
        array_unshift($track_paths, $comm_two);
        array_unshift($track_paths, $comm_one);
        array_unshift($track_paths, $jingle);

        $this->mpd->cropQueue();

        foreach ($track_paths as $path) {
            $this->logger->info(self::NAME . ": ставлю в очередь файл {$path}");

            $this->mpd->addToQueue($path);
        }
    }
}
