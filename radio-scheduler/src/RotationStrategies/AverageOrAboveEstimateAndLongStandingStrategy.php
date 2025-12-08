<?php

namespace Ridouchire\RadioScheduler\RotationStrategies;

use Monolog\Logger;
use Ridouchire\RadioScheduler\ARotationStrategy;
use Ridouchire\RadioScheduler\Services\Mpd;
use Ridouchire\RadioScheduler\TracklistGenerators\AverageEstimateTracklistGenerator;

class AverageOrAboveEstimateAndLongStandingStrategy extends ARotationStrategy
{
    public const NAME = self::class;

    public function __construct(
        private Mpd $mpd,
        private Logger $logger,
        private AverageEstimateTracklistGenerator $average_estimate_tracklist_generator
    ) {
        parent::__construct($mpd, $logger);
    }

    protected function buildTrackList(array $genres): array
    {
        return $this->average_estimate_tracklist_generator->build($genres, 8, 12);
    }
}
