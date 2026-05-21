<?php

namespace IH\DTO;

use IH\DTO\DTO;

final class SupportedMimetypes implements DTO
{
    public function __construct(
        private array $mimetypes
    ) {
    }

    public function toArray(): array
    {
        return get_object_vars($this);
    }
}
