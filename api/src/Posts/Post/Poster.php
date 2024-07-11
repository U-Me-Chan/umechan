<?php

namespace PK\Posts\Post;

class Poster
{
    public static function draft(string $poster, IsVerifyPoster $is_verify): self
    {
        return new self($poster, $is_verify);
    }

    public static function fromArray(array $state): self
    {
        return new self($state['poster'], IsVerifyPoster::tryFrom($state['is_verify']));
    }

    private function __construct(
        public readonly string $poster,
        public readonly IsVerifyPoster $is_verify
    ) {
    }
}
