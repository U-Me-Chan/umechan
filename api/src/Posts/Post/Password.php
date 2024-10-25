<?php

namespace PK\Posts\Post;

class Password
{
    public static function draft(): self
    {
        return new self(bin2hex(random_bytes(20)));
    }

    public function toString(): string
    {
        return $this->password;
    }

    public function toHash(): string
    {
        return hash('sha512', $this->password);
    }

    private function __construct(
        private readonly string $password
    ) {
    }
}
