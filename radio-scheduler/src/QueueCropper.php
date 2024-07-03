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
        $time = date('Gi', $timestamp);

        switch($time) {
            case '000':
            case '600':
            case '900':
            case '1900':
                return $this->mpd->cropQueue();
            default:
                throw new Exception('Ещё не время');
        }
    }
}
