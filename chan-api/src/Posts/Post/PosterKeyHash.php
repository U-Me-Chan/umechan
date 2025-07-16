<?php

namespace PK\Posts\Post;

class PosterKeyHash
{
    public static function fromString(string $key): self
    {
        return new self(hash('sha512', $key));
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
