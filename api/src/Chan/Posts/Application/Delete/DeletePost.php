<?php

namespace PK\Application\Commands;

use PK\Application\ICommand;

class DeletePost implements ICommand
{
    public function __construct(
        public readonly int $post_id,
        public readonly string $password
    ) {
    }

    public function toArray(): array
    {
        return get_object_vars($this);
    }
}
