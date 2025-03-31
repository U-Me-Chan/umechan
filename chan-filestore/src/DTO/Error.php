<?php

namespace IH\DTO;

use IH\DTO\DTO;

final class Error implements DTO
{
    public function __construct(
        private string $message
    ) {
    }

    public function toArray(): array
    {
        return [
            'error' => $this->message
        ];
    }
}
