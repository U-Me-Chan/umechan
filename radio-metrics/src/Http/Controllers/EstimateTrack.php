<?php

namespace Ridouchire\RadioMetrics\Http\Controllers;

use Psr\Http\Message\ServerRequestInterface;
use React\Http\Message\Response;
use Ridouchire\RadioMetrics\Exceptions\EntityNotFound;
use Ridouchire\RadioMetrics\Storage\TrackRepository;
use Ridouchire\RadioMetrics\Utils\Container;

final class EstimateTrack
{
    public function __construct(
        private TrackRepository $track_repo,
        private Container $cache
    ) {
    }

    public function __invoke(ServerRequestInterface $req, array $vars): Response
    {
        $body = $req->getBody()->getContents();
        $query_params = json_decode($body, true);

        $client_addr = $req->getServerParams()['REMOTE_ADDR'];

        try {
            $last_time_estimate = $this->cache->$client_addr;
        } catch (\Exception) {
            $last_time_estimate = 0;
        }

        if ($last_time_estimate >= (time() - 5)) {
            $res = Response::json(['status' => 'failed', 'reason' => 'timeout']);
            $res = $res->withStatus(Response::STATUS_FORBIDDEN);

            return $res;
        }

        if (!isset($query_params['operator'])) {
            $res = Response::json(['status' => 'failed', 'reason' => 'operator not found']);
            $res = $res->withStatus(Response::STATUS_BAD_REQUEST);

            return $res;
        }

        try {
            /** @var Track */
            $track = $this->track_repo->findOne(['id' => $vars['id']]);
        } catch (EntityNotFound) {
            $res = Response::json(['status' => 'failed', 'reason' => 'track not found']);
            $res = $res->withStatus(Response::STATUS_NOT_FOUND);

            return $res;
        }

        if ($this->cache->current_track['id'] !== $track->getId()) {
            $res =  Response::json(['status' => 'failed', 'reason' => 'track not playing']);
            $res = $res->withStatus(Response::STATUS_BAD_REQUEST);

            return $res;
        }

        switch ($query_params['operator']) {
            case 'plus':
                $track->increaseEstimate($track->getDuration() * 3);

                break;
            case 'minus':
                $track->decreaseEstimate();

                break;

            default:
                $res = Response::json(['status' => 'failed', 'reason' => 'invalid operator:' . $query_params['operator']]);
                $res = $res->withStatus(Response::STATUS_BAD_REQUEST);

                return $res;
        }

        $this->track_repo->save($track);

        $this->cache->$client_addr = time();

        return Response::json(['status' => 'accepted', 'track' => $track->toArray()]);
    }
}
