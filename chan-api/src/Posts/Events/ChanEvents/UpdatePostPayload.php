<?php

namespace PK\Posts\Events\ChanEvents;

use PK\Events\ChanEventPayload;

final class UpdatePostPayload extends ChanEventPayload
{
    public function __construct(
        public readonly array $changed_fields = []
    ) {
    }

    public function toArray(): array
    {
        return get_object_vars($this);
    }
}
