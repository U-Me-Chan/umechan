<?php

namespace Ridouchire\RadioScheduler\Http\Controllers;

use React\Http\Message\Response;
use Ridouchire\RadioScheduler\Mpd;

final class GetQueue
{
    public function __construct(
        private Mpd $mpd
    ) {
    }

    public function __invoke(): Response
    {
        return Response::json(['queue' => $this->mpd->getQueue()]);
    }
}
