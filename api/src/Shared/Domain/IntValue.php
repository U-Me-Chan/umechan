<?php

namespace PK\Shared\Domain;

abstract class IntValue
{
    public function __construct(
        protected int $value
    ) {
    }

    final public function toInt(): int
    {
        return $this->value;
    }
}
