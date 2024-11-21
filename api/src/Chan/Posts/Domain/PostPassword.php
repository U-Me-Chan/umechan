<?php

namespace PK\Domain;

class PostPassword implements \JsonSerializable
{
    public static function createDraft(): self
    {
        return new self(hash('sha256', bin2hex(random_bytes(5))));
    }

    public static function createFromString(string $hash): self
    {
        return new self($hash);
    }

    public function jsonSerialize(): string
    {
        return $this->password;
    }

    public function toString(): string
    {
        return $this->password;
    }

    private function __construct(
        private string $password
    ) {
    }
}
