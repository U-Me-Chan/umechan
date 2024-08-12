<?php

namespace PK\Posts\Post;

class PasswordHash
{
    public static function fromString(string $hash): self
    {
        return new self($hash);
    }

    public function toString(): string
    {
        return $this->hash;
    }

    public function isEqualsTo(string $password): bool
    {
        return hash('sha512', $password) == $this->hash ? true : false;
    }

    private function __construct(
        private string $hash
    ) {
    }
}
