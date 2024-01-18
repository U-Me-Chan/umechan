<?php

namespace PK\Tracks\Track;

class Title implements \JsonSerializable
{
    public function __construct(
        private string $title
    ) {
    }

    public function jsonSerialize(): string
    {
        return $this->title;
    }

    public function toString(): string
    {
        return $this->title;
    }
}
