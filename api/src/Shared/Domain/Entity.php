<?php

namespace PK\Shared\Domain;

abstract class Entity implements \JsonSerializable
{
    public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }

    public function toArray(): array
    {
        return get_object_vars($this);
    }
}
