<?php

namespace Ridouchire\RadioScheduler\RotationStrategies;

use Monolog\Logger;
use Ridouchire\RadioScheduler\IRotation;
use Ridouchire\RadioScheduler\Mpd;

class Pattern implements IRotation
{
    public function __construct(
        private Mpd $mpd,
        private Logger $log
    ) {
    }

    public function execute(): void
    {
    }
}
