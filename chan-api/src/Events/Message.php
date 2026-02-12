<?php

namespace PK\Events;

abstract class Message
{
    public string $topic;
    public string $json;

    public function __toString(): string
    {
        return $this->json;
    }
}
