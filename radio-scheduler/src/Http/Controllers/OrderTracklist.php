<?php

namespace Ridouchire\RadioScheduler\Http\Controllers;

use DomainException;
use OpenApi\Attributes as OA;
use Psr\Http\Message\ServerRequestInterface;
use React\Http\Message\Response;
use Ridouchire\RadioScheduler\Services\OrderTrackService;
use RuntimeException;

#[OA\Post(
    path: '/radio/queue/',
    operationId: 'putRandomTrackListToQueue',
    description: 'Добавить список случайных композиций жанра в очередь',
    summary: 'Добавляет список случайных композиций в очередь',
    parameters: [
        new OA\Parameter(
            name: 'id',
            in: 'query',
            description: 'genre',
            required: true,
            schema: new OA\Schema(
                type: 'string',
            )
        )
    ]
)]
#[OA\Response(
    response: 200,
    description: 'Список поставлен в очередь',
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
#[OA\Response(
    response: 400,
    description: 'Ошибка разбора параметров запроса',
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
                        default: 'genre not set'
                    )
                ]
            )
        )
    ]
)]
#[OA\Response(
    response: 403,
    description: 'Очередь полна',
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
                        default: 'queue is full'
                    )
                ]
            )
        )
    ]
)]
#[OA\Response(
    response: 500,
    description: 'Список композиций жанра пуст',
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
                        default: 'genre is empty'
                    )
                ]
            )
        )
    ]
)]
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

        if (!isset($params['genre'])) {
            $res = Response::json(['status' => 'failed', 'reason' => 'genre no set']);
            $res = $res->withStatus(Response::STATUS_BAD_REQUEST);

            return $res;
        }

        try {
            $this->order_track_service->putTrackListInQueue($params['genre']);
        } catch (RuntimeException) {
            $res = Response::json(['status' => 'failed', 'reason' => 'genre is empty']);
            $res = $res->withStatus(Response::STATUS_INTERNAL_SERVER_ERROR);

            return $res;
        } catch (DomainException) {
            $res = Response::json(['status' => 'failed', 'reason' => 'queue is full']);
            $res = $res->withStatus(Response::STATUS_FORBIDDEN);

            return $res;
        }

        return Response::json(['status' => 'accepted']);
    }
}
