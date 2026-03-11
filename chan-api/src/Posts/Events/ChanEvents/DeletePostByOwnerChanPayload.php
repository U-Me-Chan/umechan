<?php

namespace PK\Posts\Events\ChanEvents;

use PK\Events\ChanEventPayload;

class DeletePostByOwnerChanPayload extends ChanEventPayload
{
    public function __construct(
        public readonly int $id,
        public readonly string $reason
    ) {
    }

    /**
     * @return array{
     *     id: int,
     *     reason: string
     *}
     */
    public function toArray(): array
    {
        return get_object_vars($this);
    }
}
