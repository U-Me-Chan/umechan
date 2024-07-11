<?php

namespace PK\Events\Event;

use PK\Base\Timestamp;

class Event implements \JsonSerializable
{
    public static function draft(EventType $type, ?int $post_id = null, ?int $board_id = null): self
    {
        return new self(0, $type, Timestamp::draft(), $post_id, $board_id);
    }

    public static function fromArray(array $state): self
    {
        return new self(
            $state['id'],
            EventType::tryFrom($state['event_type']),
            Timestamp::fromInt($state['timestamp']),
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
        return [
            'id'         => $this->id,
            'event_type' => $this->type->value,
            'timestamp'  => $this->timestamp->toInt(),
            'post_id'    => $this->post_id,
            'board_id'   => $this->board_id
        ];
    }

    private function __construct(
        public readonly int $id,
        public readonly EventType $type,
        public readonly Timestamp $timestamp,
        public readonly ?int $post_id,
        public readonly ?int $board_id
    ) {
    }
}
