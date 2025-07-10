<?php

namespace PK\Events\OpenApi\Schemas;

use OpenApi\Attributes as OA;
use PK\Events\Event\Event;

#[OA\Schema]
abstract class EventList
{
    #[OA\Property]
    public int $count;

    #[OA\Property(
        description: 'Event list',
        title: 'event-list',
        items: new OA\Items(ref: Event::class)
    )]
    public array $events;
}
