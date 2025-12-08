<?php

namespace Ridouchire\RadioScheduler\RotationStrategies;

use Monolog\Logger;
use Ridouchire\RadioScheduler\ARotationStrategy;
use Ridouchire\RadioScheduler\Services\Mpd;
use Ridouchire\RadioScheduler\TracklistGenerators\NewOrLongStandingTracklistGenerator;

class NewOrLongStandingStrategy extends ARotationStrategy
{
    public const NAME = self::class;

    public function __construct(
        private Mpd $mpd,
        private Logger $logger,
        private NewOrLongStandingTracklistGenerator $new_or_long_standing_tracklist_generator
    ) {
        parent::__construct($mpd, $logger);
    }

    protected function buildTrackList(array $genres): array
    {
        return $this->new_or_long_standing_tracklist_generator->build($genres, 8, 12);
    }
}
