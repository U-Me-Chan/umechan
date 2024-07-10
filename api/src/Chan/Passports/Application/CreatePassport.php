<?php

namespace PK\Application\Commands;

use PK\Application\ICommand;

class CreatePassport implements ICommand
{
    public function __construct(
        public readonly string $name,
        public readonly string $password
    ) {
    }

    public function toArray(): array
    {
        return get_object_vars($this);
    }
}
