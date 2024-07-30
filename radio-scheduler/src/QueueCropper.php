<?php

namespace Ridouchire\RadioScheduler;

use Exception;

class QueueCropper
{
    public function __construct(
        private Mpd $mpd
    ) {
    }

    public function __invoke(int $timestamp): bool
    {
        $time = date('Gis', $timestamp);

        switch($time) {
            case '00000':
            case '60000':
            case '90000':
            case '120000':
            case '190000':
                return $this->mpd->cropQueue();
            default:
                throw new Exception('Ещё не время');
        }
    }
}
