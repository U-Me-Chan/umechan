<?php

namespace PK\Database\Event;

class Event implements \JsonSerializable
{
    public function __construct(
        private int $id,
        private string $event_type,
        private int $timestamp,
        private int|null $post_id,
        private int|null $board_id
    ) {
    }

    // fixme: как будто этот стандартный метод надо вытащить в интерфейс с генериком
    public static function fromState(array $state): self
    {
        return new self($state["id"], $state["event_type"], $state["timestamp"], $state["post_id"], $state["board_id"]);
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getEventType(): string
    {
        return $this->event_type;
    }

    public function getTimestamp(): int
    {
        return $this->timestamp;
    }

    public function getPostId(): int|null
    {
        return $this->post_id;
    }

    public function getBoardId(): int|null
    {
        return $this->board_id;
    }

    // fixme: как будто этот стандартный метод надо вытащить в интерфейс с генериком
    public function toArray(): array
    {
        return [
            "id"         => $this->id,
            "event_type" => $this->event_type,
            "timestamp"  => $this->timestamp,
            "post_id"    => $this->post_id,
            "board_id"   => $this->board_id,
        ];
    }

    public function jsonSerialize(): array
    {
        return [
            "id"         => $this->id,
            "event_type" => $this->event_type,
            "timestamp"  => $this->timestamp,
            "post_id"    => $this->post_id,
            "board_id"   => $this->board_id,
        ];
    }
}
