<?php

namespace PK\Shared\Domain;

abstract class StringValue
{
    public function __construct(
        protected string $value
    ) {
    }

    final public function toString(): string
    {
        return $this->value;
    }
}
