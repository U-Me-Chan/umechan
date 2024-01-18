<?php

namespace PK\Tracks\Track;

class Artist implements \JsonSerializable
{
    public function toString(): string
    {
        return $this->artist;
    }

    public function jsonSerialize(): string
    {
        return $this->artist;
    }

    public function __construct(
        private string $artist
    ) {
    }
}
