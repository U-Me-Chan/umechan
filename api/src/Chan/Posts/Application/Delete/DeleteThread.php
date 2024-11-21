<?php

namespace PK\Application\Commands;

use PK\Application\ICommand;

class DeleteThread implements ICommand
{
    public function __construct(
        public readonly int $thread_id,
        public readonly string $password
    ) {
    }

    public function toArray(): array
    {
        return get_object_vars($this);
    }
}
