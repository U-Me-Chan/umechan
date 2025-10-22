<?php

namespace IH;

use JsonSerializable;

readonly class File implements JsonSerializable
{
    public function __construct(
        public string $name,
        public string $original,
        public string $thumbnail,
        public array $post_ids = []
    ) {
    }

    public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }
}
