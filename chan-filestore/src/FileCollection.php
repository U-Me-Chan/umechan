<?php

namespace IH;

use JsonSerializable;

readonly class FileCollection implements JsonSerializable
{
    public function __construct(
        public array $files,
        public int $count,
        public array $filters = []
    ) {
    }

    public function jsonSerialize(): mixed
    {
        return get_object_vars($this);
    }
}
