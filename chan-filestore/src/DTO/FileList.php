<?php

namespace IH\DTO;

use IH\DTO\DTO;

final class FileList implements DTO
{
    public function __construct(
        private array $files,
        private int $count
    ) {
    }

    public function toArray(): array
    {
        return get_object_vars($this);
    }
}
