<?php

namespace PK\Events;

abstract class ChanEventPayload
{
    abstract public function toArray(): array;
}
