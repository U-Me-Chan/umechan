<?php

namespace PK\Events;

use SensitiveParameter;
use PK\Events\ChanEvent;

final class ChanEventBuilder
{
    public function __construct(
        #[SensitiveParameter]
        private string $nodeSign
    ) {
    }

    public function build(string $event_class, ChanEventPayload $payload): ChanEvent
    {
        return new $event_class($this->nodeSign, $payload);
    }
}
