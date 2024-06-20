<?php

namespace PK\Events\Event;

class Event implements \JsonSerializable
{
    public static function fromArray(array $state): self
    {
        return new self($state["id"], $state["event_type"], $state["timestamp"], $state["post_id"], $state["board_id"]);
    }

    public function jsonSerialize(): array
    {
        $data = get_object_vars($this);
        return $data;
    }

    public function toArray(): array
    {
        $data = get_object_vars($this);
        return $data;
    }

    private function __construct(
        public int $id,
        public string $event_type,
        public int $timestamp,
        public int|null $post_id,
        public int|null $board_id
    ) {
    }
}
