<?php

namespace PK\Tracks\Controllers;

use PK\Http\Request;
use PK\Http\Response;
use PK\Tracks\TrackRepository;
use OpenApi\Attributes as OA;

final class GetTrackList
{
    public function __construct(
        private TrackRepository $track_repo
    ) {
    }

    #[OA\Get(path: '/api/radio/tracks', description: 'Возвращает список композиций на радиостанции')]
    #[OA\Response(
        response: 200,
        description: 'Ответ содержит список композиция и их количество',
        content: new OA\JsonContent(
            title: 'Схема ответа',
            properties: [
                new OA\Property(
                    property: 'payload',
                    type: 'object',
                    properties: [
                        new OA\Property(
                            property: 'count',
                            type: 'integer',
                            title: 'Количество композиций'
                        ),
                        new OA\Property(
                            property: 'tracks',
                            type: 'array',
                            items: new OA\Items(ref: '#/components/schemas/Track')
                        )
                    ]
                )
            ]
        )
    )]
    public function __invoke(Request $req): Response
    {
        list($tracks, $count) = $this->track_repo->findMany($req->getParams());

        return new Response(['tracks' => $tracks, 'count' => $count]);
    }
}
