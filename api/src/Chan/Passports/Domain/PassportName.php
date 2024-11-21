<?php

namespace PK\Domain;

class PassportName implements \JsonSerializable
{
    public static function draft(string $name): self
    {
        return new self($name);
    }

    public static function fromString(string $name): self
    {
        return new self($name);
    }

    public function jsonSerialize(): string
    {
        return $this->name;
    }

    public function toString(): string
    {
        return $this->name;
    }

    private function __construct(
        private string $name
    ) {
    }
}
