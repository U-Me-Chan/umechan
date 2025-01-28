<?php

namespace IH\DTO;

use IH\DTO\DTO;

final class FileInfo implements DTO
{
    public function __construct(
        private string $original,
        private string $thumbnail,
        private array $post_ids = []
    ) {
    }

    public function toArray(): array
    {
        return get_object_vars($this);
    }
}
