<?php

namespace PK\Application\Commands;

use PK\ICommand;

class RevokePassport implements ICommand
{
    public function __construct(
        public readonly string $password
    ) {
    }

    public function toArray(): array
    {
        return get_object_vars($this);
    }
}
