<?php

namespace Ridouchire\RadioScheduler\Http\Controllers;

use DomainException;
use OutOfBoundsException;
use RuntimeException;
use OpenApi\Attributes as OA;
use Psr\Http\Message\ServerRequestInterface;
use React\Http\Message\Response;
use Ridouchire\RadioScheduler\Services\OrderTrackService;

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
        private OrderTrackService $order_track_service
    ) {
    }

    public function __invoke(ServerRequestInterface $req): Response
    {
        $body   = $req->getBody()->getContents();
        $params = json_decode($body, true);

        try {
            $this->order_track_service->putTrackInQueue($params['track_id']);

            return Response::json(['status' => 'accepted']);
        } catch (OutOfBoundsException) {
            $res = Response::json(['status' => 'failed', 'reason' => 'track not found']);
            $res = $res->withStatus(Response::STATUS_BAD_REQUEST);

            return $res;
        } catch (RuntimeException) {
            $res = Response::json(['status' => 'failed', 'reason' => 'track no add']);
            $res = $res->withStatus(Response::STATUS_INTERNAL_SERVER_ERROR);

            return $res;
        } catch (DomainException) {
            $res = Response::json(['status' => 'failed', 'reason' => 'queue is full']);
            $res = $res->withStatus(Response::STATUS_BAD_REQUEST);

            return $res;
        }
    }
}
