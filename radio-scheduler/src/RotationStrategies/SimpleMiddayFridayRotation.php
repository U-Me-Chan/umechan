<?php

namespace Ridouchire\RadioScheduler\RotationStrategies;

use Monolog\Logger;
use Ridouchire\RadioScheduler\IRotation;
use Ridouchire\RadioScheduler\Services\Mpd;
use Ridouchire\RadioScheduler\TracklistGenerators\AverageEstimateTracklistGenerator;
use Ridouchire\RadioScheduler\TracklistGenerators\NewOrLongStandingTracklistGenerator;

class SimpleMiddayFridayRotation implements IRotation
{
    public const NAME = 'SimpleMiddayFridayRotation';

    public function __construct(
        private Mpd $mpd,
        private Logger $logger,
        private NewOrLongStandingTracklistGenerator $new_or_long_standing_tracklist_generator,
        private AverageEstimateTracklistGenerator $average_estimate_tracklist_generator
    ) {
    }

    public function isFired(int $hour = 0): bool
    {
        $weekday = date('w');
        $day     = date('d');

        if (($day % 2) == 0 && $weekday == 5) {
            return true;
        }

        return false;
    }

    public function execute(): void
    {
        $a = $this->average_estimate_tracklist_generator->build(['DnB Pop'], 2, 2);
        $b = $this->new_or_long_standing_tracklist_generator->build(['DnB Pop'], 3, 3, $a);

        $track_paths = array_merge($a, $b);

        array_walk($track_paths, function (string $track_path) {
            $this->logger->info(self::NAME . ": ставлю в очередь {$track_path}");

            if (!$this->mpd->addToQueue($track_path)) {
                $this->logger->error(self::NAME . ": ошибка постановки в очередь {$track_path}");
            }
        });
    }
}
