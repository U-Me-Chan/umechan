<?php

namespace PK\Events\OpenApi\Schemas;

use OpenApi\Attributes as OA;
use PK\Events\Event;

#[OA\Schema]
abstract class EventList
{
    #[OA\Property(description: 'Общее количество')]
    public int $count;

    #[OA\Property(
        description: 'Список событий',
        items: new OA\Items(ref: Event::class)
    )]
    public array $events;
}
