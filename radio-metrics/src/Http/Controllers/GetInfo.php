<?php

namespace Ridouchire\RadioMetrics\Http\Controllers;

use React\Http\Message\Response;
use Ridouchire\RadioMetrics\ICache;

final class GetInfo
{
    public function __construct(
        private ICache $cache
    ) {
    }

    public function __invoke(): Response
    {
        return Response::json($this->cache->get('current_track'));
    }
}
