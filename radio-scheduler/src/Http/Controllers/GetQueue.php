<?php

namespace Ridouchire\RadioScheduler\Http\Controllers;

use OpenApi\Attributes as OA;
use React\Http\Message\Response;
use Ridouchire\RadioScheduler\Http\OpenApi\Schemas\Queue;
use Ridouchire\RadioScheduler\Mpd;

#[OA\Get(
    path: '/radio/queue',
    operationId: 'getQueue',
    description: 'Возвращает список композиций в очереди воспроизведения',
    summary: 'Получить текущую очередь воспроизведения'
)]
#[OA\Response(
    response: 200,
    description: 'Возвращает список композиций в очереди воспроизведения',
    content: [new OA\MediaType(mediaType: 'application/json', schema: new OA\Schema(ref: Queue::class))]
)]
final class GetQueue
{
    public function __construct(
        private Mpd $mpd
    ) {
    }

    public function __invoke(): Response
    {
        return Response::json(['queue' => $this->mpd->getQueue()]);
    }
}
