<?php

namespace Ridouchire\RadioScheduler\RotationStrategies;

use Monolog\Logger;
use Ridouchire\RadioScheduler\IRotation;
use Ridouchire\RadioScheduler\Services\Mpd;
use Ridouchire\RadioScheduler\TracklistGenerators\AverageEstimateTracklistGenerator;
use Ridouchire\RadioScheduler\TracklistGenerators\NewOrLongStandingTracklistGenerator;
use Ridouchire\RadioScheduler\TracklistGenerators\RandomTracklistGenerator;

class OddMiddayFridayRotation implements IRotation
{
    public const NAME = 'OddMiddayFridayRotation';

    public function __construct(
        private Mpd $mpd,
        private Logger $logger,
        private NewOrLongStandingTracklistGenerator $new_or_long_standing_tracklist_generator,
        private AverageEstimateTracklistGenerator $average_estimate_tracklist_generator,
        private RandomTracklistGenerator $random_tracklist_generator
    ) {
    }

    public function isFired(int $hour = 0): bool
    {
        $weekday = date('w');
        $day     = date('d');

        if (($day % 2) !== 0 && $weekday == 5) {
            return true;
        }

        return false;
    }

    public function execute(): void
    {
        $a = $this->average_estimate_tracklist_generator->build(['Dancecore'], 2, 2);
        $b = $this->new_or_long_standing_tracklist_generator->build(['Dancecore'], 3, 3);
        $c = $this->random_tracklist_generator->build(['Dancecore Breaker'], 1);

        $track_paths = array_merge($a, $b, $c);

        array_walk($track_paths, function (string $track_path) {
            $this->logger->info(self::NAME . ": ставлю в очередь {$track_path}");

            if (!$this->mpd->addToQueue($track_path)) {
                $this->logger->error(self::NAME . ": ошибка постановки в очередь {$track_path}");
            }
        });
    }
}
