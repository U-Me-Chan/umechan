<?php

namespace PK\Base;

class Timestamp implements \JsonSerializable
{
    public static function draft(): self
    {
        return new self(time());
    }

    public static function fromInt(int $unixtime): self
    {
        return new self($unixtime);
    }

    public function jsonSerialize(): string
    {
        return date('d-m-Y H:i:s', $this->timestamp);
    }

    public function toInt(): int
    {
        return $this->timestamp;
    }

    private function __construct(
        private int $timestamp
    ) {
    }
}
