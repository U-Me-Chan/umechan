<?php

namespace Ridouchire\RadioScheduler\Http\Controllers;

use Psr\Http\Message\ServerRequestInterface;
use React\Http\Message\Response;
use Ridouchire\RadioScheduler\Services\OrderTrackService;

final class OrderTracklist
{
    public function __construct(
        private OrderTrackService $order_track_service
    ) {
    }

    public function __invoke(ServerRequestInterface $req): Response
    {
        $body   = $req->getBody()->getContents();
        $params = json_decode($body, true);

        $this->order_track_service->putTrackListInQueue($params['genre']);

        return Response::json(['status' => 'accepted']);
    }
}
