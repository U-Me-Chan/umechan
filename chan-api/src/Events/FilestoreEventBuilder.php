<?php

namespace PK\Events;

use SensitiveParameter;
use PK\Events\FilestoreEvent;

final class FilestoreEventBuilder
{
    public function __construct(
        #[SensitiveParameter]
        private string $nodeSign
    ) {
    }

    public function build(string $event_class, FilestoreEventPayload $payload): FilestoreEvent
    {
        return new $event_class($this->nodeSign, $payload);
    }
}
