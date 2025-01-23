<?php

namespace Ridouchire\RadioScheduler\Http\Controllers;

use Medoo\Medoo;
use Monolog\Logger;
use Psr\Http\Message\ServerRequestInterface;
use React\Http\Message\Response;
use Ridouchire\RadioScheduler\Mpd;

final class OrderTrack
{
    public function __construct(
        private Mpd $mpd,
        private Medoo $db,
        private Logger $log
    ) {
    }

    public function __invoke(ServerRequestInterface $req): Response
    {
        $body   = $req->getBody()->getContents();
        $params = json_decode($body, true);

        if (!isset($params['track_id'])) {
            $res = Response::json(['status' => 'failed', 'reason' => 'track_id not found']);
            $res = $res->withStatus(Response::STATUS_BAD_REQUEST);

            return $res;
        }

        $track_path = $this->db->get('tracks', 'path', ['id' => $params['track_id']]);

        if (!$track_path) {
            $res = Response::json(['status' => 'failed', 'reason' => 'track not found']);
            $res = $res->withStatus(Response::STATUS_BAD_REQUEST);

            return $res;
        }

        $result = $this->mpd->addToQueueAfterCurrentTrack($track_path);

        if (!$result) {
            $res = Response::json(['status' => 'failed', 'reason' => 'track no add']);
            $res = $res->withStatus(Response::STATUS_INTERNAL_SERVER_ERROR);

            return $res;
        }

        $this->log->info("OrderTrack: ставлю в очередь файл {$track_path}");

        return Response::json(['status' => 'accepted']);
    }
}
