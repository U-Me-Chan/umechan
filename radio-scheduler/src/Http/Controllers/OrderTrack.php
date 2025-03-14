<?php

namespace Ridouchire\RadioScheduler\Http\Controllers;

use OpenApi\Attributes as OA;
use Medoo\Medoo;
use Monolog\Logger;
use Psr\Http\Message\ServerRequestInterface;
use React\Http\Message\Response;
use Ridouchire\RadioScheduler\Mpd;

#[OA\Put(
    path: '/radio/queue/{id}',
    operationId: 'putTrackToQueue',
    description: 'Добавить композицию в очередь',
    summary: 'Добавить композицию в очередь',
    parameters: [
        new OA\Parameter(
            name: 'id',
            in: 'path',
            description: 'Идентификатор композиции',
            required: true,
            schema: new OA\Schema(
                type: 'integer',
                format: 'int64'
            )
        )
    ]
)]
#[OA\Response(
    response: 400,
    description: 'Если трек не найден',
    content: [
        new OA\MediaType(
            mediaType: 'application/json',
            schema: new OA\Schema(
                properties: [
                    new OA\Property(
                        property: 'status',
                        type: 'string',
                        default: 'failed'
                    ),
                    new OA\Property(
                        property: 'reason',
                        type: 'string',
                        default: 'track not found'
                    )
                ]
            )
        )
    ]
)]
#[OA\Response(
    response: 500,
    description: 'Если при добавлении композиции в очередь произошла ошибка',
    content: [
        new OA\MediaType(
            mediaType: 'application/json',
            schema: new OA\Schema(
                properties: [
                    new OA\Property(
                        property: 'status',
                        type: 'string',
                        default: 'failed'
                    ),
                    new OA\Property(
                        property: 'reason',
                        type: 'string',
                        default: 'track not add'
                    )
                ]
            )
        )
    ]
)]
#[OA\Response(
    response: 200,
    description: 'Композиция поставлена в очередь',
    content: [
        new OA\MediaType(
            mediaType: 'application/json',
            schema: new OA\Schema(
                properties: [
                    new OA\Property(
                        property: 'status',
                        type: 'string',
                        default: 'accepted'
                    )
                ]
            )
        )
    ]
)]
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

        $track_path = $this->db->get('tracks', 'path', ['id' => $params['track_id']]);

        if (!$track_path) {
            $res = Response::json(['status' => 'failed', 'reason' => 'track not found']);
            $res = $res->withStatus(Response::STATUS_BAD_REQUEST);

            return $res;
        }

        $result = $this->mpd->addToQueue($track_path, 1);

        if (!$result) {
            $res = Response::json(['status' => 'failed', 'reason' => 'track no add']);
            $res = $res->withStatus(Response::STATUS_INTERNAL_SERVER_ERROR);

            return $res;
        }

        $this->log->info("OrderTrack: ставлю в очередь файл {$track_path}");

        return Response::json(['status' => 'accepted']);
    }
}
