<?php

namespace PK\Boards\Board;

use InvalidArgumentException;
use JsonSerializable;

enum PublicFlag implements JsonSerializable
{
    case yes;
    case no;

    public static function fromString(string $value): self
    {
        return match ($value) {
            self::yes->name => self::yes,
            self::no->name  => self::no,
            default         => throw new InvalidArgumentException()
        };
    }

    public static function fromBool(bool $state): self
    {
        return match ($state) {
            true    => self::yes,
            false   => self::no
        };
    }

    public function jsonSerialize(): bool
    {
        return $this == self::yes ? true : false;
    }
}
