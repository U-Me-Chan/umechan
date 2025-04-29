<?php

namespace PK\Events\Event;

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
        public int $id,
        public string $event_type,
        public int $timestamp,
        public ?int $post_id,
        public ?int $board_id
    ) {
    }
}
