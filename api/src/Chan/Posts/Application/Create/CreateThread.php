<?php

namespace PK\Application\Commands;

use PK\ICommand;

final class CreateThread implements ICommand
{
    public function __construct(
        public readonly string $poster,
        public readonly string $subject,
        public readonly string $message,
        public readonly string $tag
    ) {
    }

    public function toArray(): array
    {
        return get_object_vars($this);
    }
}
