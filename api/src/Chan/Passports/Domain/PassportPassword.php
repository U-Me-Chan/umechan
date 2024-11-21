<?php

namespace PK\Domain;

class PassportPassword implements \JsonSerializable
{
    public static function draft(string $password): self
    {
        return new self(hash('sha512', $password));
    }

    public static function fromString(string $hash): self
    {
        return new self($hash);
    }

    public function jsonSerialize(): string
    {
        return $this->hash;
    }

    public function toString(): string
    {
        return $this->hash;
    }

    private function __construct(
        private string $hash
    ) {
    }
}
