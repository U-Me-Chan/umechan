<?php

namespace PK\Chan\Boards\Application\Commands;

use PK\Shared\Infrastructrure\ICommand;

class DeleteBoardCommand implements ICommand
{
    public function __construct(
        public readonly string $tag
    ) {
    }

    public function toArray(): array
    {
        return get_object_vars($this);
    }
}
