<?php

namespace PK\Events;

abstract class ChanEventPayload
{
    /**
     * @phpstan-ignore missingType.iterableValue
     */
    abstract public function toArray(): array;
}
