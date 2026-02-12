<?php

namespace PK\Events;

abstract class FilestoreEventPayload
{
    abstract public function toArray(): array;
}
