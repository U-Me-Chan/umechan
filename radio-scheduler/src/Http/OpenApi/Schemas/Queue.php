<?php

namespace Ridouchire\RadioScheduler\Http\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    description: 'Queue model',
    title: 'Queue model'
)]
abstract class Queue
{
    #[OA\Property(
        description: 'Track list',
        title: 'queue',
        items: new OA\Items(ref: QueueTrack::class)
    )]
    private array $queue;


    private function __clone() {}
    private function __construct() {}
}
