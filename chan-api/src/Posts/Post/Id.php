<?php

namespace PK\Posts\Post;

class Id
{
    public static function generate(): self
    {
        return new self(intval(microtime(true) * 1000000));
    }

    public static function fromInt(int $id): self
    {
        return new self($id);
    }

    private function __construct(
        public readonly int $value
    ) {
    }
}
