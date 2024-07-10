<?php

namespace PK\Application\Commands;

use PK\ICommand;

class CreateThreadReply implements ICommand
{
    public function __construct(
        public readonly int $thread_id,
        public readonly string $messsage,
        public readonly string $poster,
        public readonly string $subject = ''
    ) {}

    public function toArray(): array
    {
        return get_object_vars($this);
    }
}
