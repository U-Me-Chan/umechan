<?php

namespace Ridouchire\RadioScheduler\RotationStrategies;

use Monolog\Logger;
use Ridouchire\RadioScheduler\Mpd;

class Pattern
{
    public function __construct(
        private Mpd $mpd,
        private Logger $log
    ) {
    }
}
