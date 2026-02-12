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

    public function toArray(): array
    {
        return get_object_vars($this);
    }
}
