<?php

namespace Ridouchire\RadioMetrics\Http\Controllers;

use Psr\Http\Message\ServerRequestInterface;
use React\Http\Message\Response;
use Ridouchire\RadioMetrics\Utils\Container;

final class GetInfo
{
    public function __construct(
        private Container $cache
    ) {
    }

    public function __invoke(ServerRequestInterface $req, array $vars = []): Response
    {
        return Response::json($this->cache->current_track);
    }
}
