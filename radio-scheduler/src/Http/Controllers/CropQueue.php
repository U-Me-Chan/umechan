<?php

namespace Ridouchire\RadioScheduler\Http\Controllers;

use OpenApi\Attributes as OA;
use React\Http\Message\Response;
use Ridouchire\RadioScheduler\Services\QueueCropper;

#[OA\Delete(
    path: '/radio/queue',
    operationId: 'cropQueue',
    description: 'Очистить очередь воспроизведения',
    summary: 'Очищает очередь воспроизведения',
)]
#[OA\Response(
    response: 500,
    description: 'При очищении очереди произошла ошибка',
    content: [
        new OA\MediaType(
            mediaType: 'application/json',
            schema: new OA\Schema(
                properties: [
                    new OA\Property(
                        property: 'status',
                        type: 'string',
                        default: 'failed'
                    )
                ]
            )
        )
    ]
)]
#[OA\Response(
    response: 200,
    description: 'Очередь воспроизведения очищена',
    content: [
        new OA\MediaType(
            mediaType: 'application/json',
            schema: new OA\Schema(
                properties: [
                    new OA\Property(
                        property: 'status',
                        type: 'string',
                        default: 'done'
                    )
                ]
            )
        )
    ]
)]
final class CropQueue
{
    public function __construct(
        private QueueCropper $queue_cropper
    ) {
    }

    public function __invoke(): Response
    {
        $res = $this->queue_cropper->manualCrop();

        if ($res == false) {
            $res = Response::json(['status' => 'failed']);
            $res = $res->withStatus(Response::STATUS_INTERNAL_SERVER_ERROR);

            return $res;
        }

        return Response::json(['status' => 'done']);
    }
}
