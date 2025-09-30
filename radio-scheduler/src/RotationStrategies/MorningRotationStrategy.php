<?php

namespace Ridouchire\RadioScheduler\RotationStrategies;

use Monolog\Logger;
use Ridouchire\RadioScheduler\GenreSchemas\Morning;
use Ridouchire\RadioScheduler\IRotation;
use Ridouchire\RadioScheduler\Services\Mpd;
use Ridouchire\RadioScheduler\TracklistGenerators\RandomTracklistGenerator;

class MorningRotationStrategy implements IRotation
{
    public const NAME = 'MorningRotation';

    public function __construct(
        private Mpd $mpd,
        private Logger $logger,
        private RandomTracklistGenerator $random_tracklist_generator
    ) {
    }

    public function isFired(int $hour = 0): bool
    {
        return true;
    }

    public function execute(): void
    {
        $jingle_paths      = $this->random_tracklist_generator->build(['Jingles'], 1);
        $commercials_paths = $this->random_tracklist_generator->build(['Commercials'], 3);
        $track_paths       = $this->random_tracklist_generator->build([Morning::getRandom()], 10);

        $track_paths = array_merge($jingle_paths, $commercials_paths, $track_paths);

        array_walk($track_paths, function (string $track_path) {
            $this->logger->info(self::NAME . ": ставлю в очередь {$track_path}");

            if (!$this->mpd->addToQueue($track_path)) {
                $this->logger->error(self::NAME . ": ошибка постановки в очередь {$track_path}");
            }
        });
    }
}
