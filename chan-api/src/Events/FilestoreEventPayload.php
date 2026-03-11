<?php

namespace PK\Events;

abstract class FilestoreEventPayload
{
    /**
     * @phpstan-ignore missingType.iterableValue
     */
    abstract public function toArray(): array;
}
