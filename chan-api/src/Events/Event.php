<?php

namespace PK\Events;

use OpenApi\Attributes as OA;
use PK\Events\Event\EventType;

#[OA\Schema]
class Event implements \JsonSerializable
{
    public static function draft(EventType $type, ?int $post_id = null, ?int $board_id = null): self
    {
        return new self(
            0,
            $type->name,
            time(),
            $post_id,
            $board_id
        );
    }

    public static function fromArray(array $state): self
    {
        return new self(
            $state['id'],
            $state['event_type'],
            $state['timestamp'],
            $state['post_id'],
            $state['board_id']
        );
    }

    public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }

    public function toArray(): array
    {
        return get_object_vars($this);
    }

    private function __construct(
        #[OA\Property(description: 'Идентификатор')]
        public int $id,
        #[OA\Property(description: 'Тип')]
        public string $event_type,
        #[OA\Property(description: 'Метка времени')]
        public int $timestamp,
        #[OA\Property(description: 'Идентификатор поста', default: null)]
        public ?int $post_id,
        #[OA\Property(description: 'Идентификатор доски', default: null)]
        public ?int $board_id
    ) {
    }
}
