<?php

namespace PK\Domain;

use PK\Base\Timestamp;
use PK\Domain\ChanEventType;

class Event implements \JsonSerializable
{
    public static function createDraft(
        ChanEventType $type,
        ?int $post_id = null,
        ?int $board_id
    ): self
    {
        return new self(
            0,
            $type,
            Timestamp::createDraft(),
            $post_id,
            $board_id
        );
    }

    public function createFromArray(array $state): self
    {
        return new self(
            $state['id'],
            ChanEventType::tryFrom($state['event_type']),
            Timestamp::createFromInt($state['timestamp']),
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
        private int $id,
        private ChanEventType $type,
        private Timestamp $timestamp,
        public ?int $post_id,
        public ?int $board_id
    ) {
    }
}
