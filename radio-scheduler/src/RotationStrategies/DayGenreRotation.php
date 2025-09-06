<?php

namespace Ridouchire\RadioScheduler\RotationStrategies;

use Monolog\Logger;
use Ridouchire\RadioScheduler\GenreSchemas\Day;
use Ridouchire\RadioScheduler\IRotation;
use Ridouchire\RadioScheduler\Services\Mpd;
use Ridouchire\RadioScheduler\TracklistGenerators\AverageEstimateTracklistGenerator;
use Ridouchire\RadioScheduler\TracklistGenerators\NewOrLongStandingTracklistGenerator;
use Ridouchire\RadioScheduler\TracklistGenerators\RandomTracklistGenerator;

class DayGenreRotation implements IRotation
{
    public const NAME = 'DayGenreRotation';

    public function __construct(
        private Mpd $mpd,
        private Logger $logger,
        private RandomTracklistGenerator $random_tracklist_generator,
        private NewOrLongStandingTracklistGenerator $new_or_long_standing_tracklist_generator,
        private AverageEstimateTracklistGenerator $average_estimate_tracklist_generator
    ) {
    }

    public function isFired(int $hour = 9): bool
    {
        return ($hour % 2) == 0 ? false : true;
    }

    public function execute(): void
    {
        $genre = Day::getRandom();

        $jingle_paths      = $this->random_tracklist_generator->build(['Jingles'], 1);
        $commercials_paths = $this->random_tracklist_generator->build(['Commercials'], 3);
        $new_track_paths   = $this->new_or_long_standing_tracklist_generator->build([$genre], 4, 8);
        $avg_track_paths   = $this->average_estimate_tracklist_generator->build([$genre], 4, 8);

        $track_paths = array_merge($new_track_paths, $avg_track_paths);

        shuffle($track_paths);

        $track_paths = array_merge($jingle_paths, $commercials_paths, $track_paths);

        array_walk($track_paths, function (string $track_path) {
            if ($track_path == null) {
                return;
            }

            $this->mpd->addToQueue($track_path);

            $this->logger->info(self::NAME . ": ставлю в очередь {$track_path}");
        });
    }
}
