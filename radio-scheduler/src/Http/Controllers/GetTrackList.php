<?php

namespace Ridouchire\RadioScheduler\Http\Controllers;

use Medoo\Medoo;
use Psr\Http\Message\ServerRequestInterface;
use React\Http\Message\Response;

final class GetTrackList
{
    public function __construct(
        private Medoo $db
    ) {
    }

    public function __invoke(ServerRequestInterface $req): Response
    {
        $params = $req->getQueryParams();

        $limit = [
            'LIMIT' => [
                isset($params['offset']) ? $params['offset'] : 0,
                isset($params['limit']) ? $params['limit'] : 10
            ]
        ];

        $conditions = [];

        if (isset($params['artist_substr'])) {
            $conditions['artist[~]'] = "%{$params['artist_substr']}%";
        }

        if (isset($params['title_substr'])) {
            $conditions['title[~]'] = "%{$params['title_substr']}%";
        }

        $order = [
            'ORDER' => [
                'artist' => 'ASC',
                'title'  => 'ASC'
            ]
        ];

        $count = $this->db->count('tracks', $conditions);
        $tracks = $this->db->select('tracks', '*', array_merge($conditions, $limit, $order));

        return Response::json(['tracks' => $tracks, 'count' => $count]);
    }
}
