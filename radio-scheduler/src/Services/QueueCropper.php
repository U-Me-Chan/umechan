<?php

namespace Ridouchire\RadioScheduler\Services;

use Exception;
use Ridouchire\RadioScheduler\Services\Mpd;

class QueueCropper
{
    public function __construct(
        private Mpd $mpd
    ) {
    }

    public function manualCrop(): bool
    {
        return $this->mpd->cropQueue();
    }

    public function __invoke(int $timestamp): bool
    {
        $time = date('Gis', $timestamp);

        switch($time) {
            case '00000':
            case '60000':
            case '90000':
            case '190000':
                return $this->mpd->cropQueue();
            default:
                throw new Exception('Ещё не время');
        }
    }
}
