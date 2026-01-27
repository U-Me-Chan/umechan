<?php

namespace Ridouchire\RadioScheduler\RotationStrategies;

use Monolog\Logger;
use Ridouchire\RadioScheduler\GenreSchemas\Night;
use Ridouchire\RadioScheduler\IRotation;
use Ridouchire\RadioScheduler\Services\Mpd;
use Ridouchire\RadioScheduler\TracklistGenerators\AverageEstimateTracklistGenerator;
use Ridouchire\RadioScheduler\TracklistGenerators\NewOrLongStandingTracklistGenerator;

class NightGenreRotation implements IRotation
{
    public const NAME = 'NightGenreRotation';

    public function __construct(
        private Mpd $mpd,
        private Logger $logger,
        private NewOrLongStandingTracklistGenerator $new_or_long_standing_tracklist_generator,
        private AverageEstimateTracklistGenerator $average_estimate_tracklist_generator
    ) {
    }

    public function isFired(int $hour = 0): bool
    {
        return true;
    }

    public function execute(): void
    {
        $genre = Night::getRandom();

        $new_track_paths = $this->new_or_long_standing_tracklist_generator->build([$genre], 4, 8);
        $avg_track_paths = $this->average_estimate_tracklist_generator->build([$genre], 4, 8, $new_track_paths);

        $track_paths = array_merge($new_track_paths, $avg_track_paths);

        shuffle($track_paths);

        array_walk($track_paths, function (string $track_path) {
            $this->logger->info(self::NAME . ": ставлю в очередь {$track_path}");

            if (!$this->mpd->addToQueue($track_path)) {
                $this->logger->error(self::NAME . ": ошибка постановки в очередь {$track_path}");
            }
        });
    }
}
