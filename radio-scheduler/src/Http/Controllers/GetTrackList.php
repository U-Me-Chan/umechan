<?php

namespace Ridouchire\RadioScheduler\Http\Controllers;

use OpenApi\Attributes as OA;
use Medoo\Medoo;
use Psr\Http\Message\ServerRequestInterface;
use React\Http\Message\Response;
use Ridouchire\RadioScheduler\Http\OpenApi\Schemas\Track;

#[OA\Get(
    path: '/radio/tracks',
    operationId: 'getTrackList',
    description: 'Возвращает список композиций на радио',
    summary: 'Поиск композиций на радио',
    parameters: [
        new OA\Parameter(
            name: 'offset',
            in: 'query',
            description: 'Смещение относительно первого элемента',
            required: false,
            schema: new OA\Schema(
                type: 'integer',
                format: 'int64'
            )
        ),
        new OA\Parameter(
            name: 'limit',
            in: 'query',
            description: 'Количество композиций в ответе',
            required: false,
            schema: new OA\Schema(
                type: 'integer',
                format: 'int64'
            )
        ),
        new OA\Parameter(
            name: 'artist_substr',
            in: 'query',
            description: 'Подстрока для поиска по исполнителю',
            required: false,
            schema: new OA\Schema(
                type: 'string'
            )
        ),
        new OA\Parameter(
            name: 'title_substr',
            in: 'query',
            description: 'Подстрока для поиска по имени композиции',
            required: false,
            schema: new OA\Schema(
                type: 'string'
            )
        ),
        new OA\Parameter(
            name: 'genre_substr',
            in: 'query',
            description: 'Подстрока для поиска по жанру(плейлисту)',
            required: false,
            schema: new OA\Schema(
                type: 'string'
            )
        )
    ]
)]
#[OA\Response(
    response: 200,
    description: 'Возвращается список треков и их общее количество',
    content: [
        new OA\MediaType(
            mediaType: 'application/json',
            schema: new OA\Schema(
                properties: [
                    new OA\Property(
                        property: 'count',
                        type: 'integer',
                        format: 'int64'
                    ),
                    new OA\Property(
                        property: 'tracks',
                        type: 'array',
                        items: new OA\Items(ref: Track::class)
                    )
                ]
            )
        )
    ]
)]
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

        if (isset($params['genre_substr'])) {
            $conditions['path[~]'] = "{$params['genre_substr']}/%";
        }

        $order = [
            'ORDER' => [
                'artist' => 'ASC',
                'title'  => 'ASC'
            ]
        ];

        $count = $this->db->count('tracks', $conditions);
        /** @phpstan-ignore arguments.count, argument.type */
        $tracks = $this->db->select('tracks', '*', array_merge($conditions, $limit, $order));

        return Response::json(['tracks' => $tracks, 'count' => $count]);
    }
}
