<?php

namespace PK\Events\Controllers;

use OpenApi\Attributes as OA;
use PK\Http\Request;
use PK\Events\EventStorage;
use PK\Events\OpenApi\Schemas\EventList;
use PK\Http\Responses\JsonResponse;
use PK\OpenApi\Schemas\Response;

#[OA\Get(
    path: '/api/v2/events',
    operationId: 'getEventList',
    summary: 'Получить список событий на чане',
    tags: ['event'],
    parameters: [
        new OA\Parameter(
            name: 'limit',
            in: 'query',
            required: false,
            description: 'Количество элементов в ответе',
            schema: new OA\Schema(
                type: 'integer',
                format: 'int64',
                default: 20
            )
        ),
        new OA\Parameter(
            name: 'offset',
            in: 'query',
            required: false,
            description: 'Смещение в списке элементов',
            schema: new OA\Schema(
                type: 'integer',
                format: 'int64',
                default: 0
            )
        ),
        new OA\Parameter(
            name: 'from_timestamp',
            in: 'query',
            description: 'Метка времени в unixtime',
            schema: new OA\Schema(
                type: 'integer',
                format: 'int64',
                maxLength: 10,
                minLength: 10,
                default: 0
            )
        )
    ]
)]
#[Response(
    response: 200,
    description: 'Cписок событий и их общее количество',
    payload_reference: EventList::class
)]
final class GetEventList
{
    public function __construct(private EventStorage $storage) {}

    public function __invoke(Request $req): JsonResponse
    {
        /** @var int */
        $limit = $req->getParams('limit', 20);

        /** @var int */
        $offset = $req->getParams('offset', 0);

        /** @var int */
        $from_timestamp = $req->getParams('from_timestamp', 0);

        list($events, $count) = $this->storage->find($limit, $offset, $from_timestamp);

        return new JsonResponse(['count' => $count, 'events' => $events], 200);
    }
}
