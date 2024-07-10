<?php

namespace PK\Domain;

class PostPoster
{
    private const DEFAULT_NAME = 'Anonymous';

    public static function createFromString(string $poster = self::DEFAULT_NAME, bool $is_verify = false): self
    {
        return new self($poster, $is_verify);
    }

    public function isVerify(): bool
    {
        return $this->is_verify;
    }

    public function getPoster(): string
    {
        return $this->poster;
    }

    private function __construct(
        private string $poster,
        private bool $is_verify
    ) {
    }
}
