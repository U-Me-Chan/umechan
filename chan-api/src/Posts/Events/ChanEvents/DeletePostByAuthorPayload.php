<?php

namespace PK\Posts\Events\ChanEvents;

use PK\Events\ChanEventPayload;

class DeletePostByAuthorPayload extends ChanEventPayload
{
    public function __construct(
        public readonly int $id
    ) {
    }

    /**
     * @return array{id:int}
     */
    public function toArray(): array
    {
        return get_object_vars($this);
    }
}
