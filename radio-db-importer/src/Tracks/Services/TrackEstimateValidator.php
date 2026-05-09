<?php

namespace Ridouchire\RadioDbImporter\Tracks\Services;

use Ridouchire\RadioDbImporter\Tracks\Track;

class TrackEstimateValidator
{
    public function __construct(
        private int $bad_estimate_value
    ) {
    }

    public function isBadEstimate(Track $track): bool
    {
        if ($track->getPlayCount() <= 3) {
            return false;
        }

        if ($track->getEstimate() < (0 - $track->getDuration() * $this->bad_estimate_value)) {
            return true;
        }

        return false;
    }
}
