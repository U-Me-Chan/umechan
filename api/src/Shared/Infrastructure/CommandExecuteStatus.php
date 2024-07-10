<?php

namespace PK\Shared\Infrastructrure;

use PK\Application\CommandStatus;

final class CommandExecuteStatus
{
    public function __construct(
        public readonly CommandStatus $status,
        public readonly array $context = [],
        public readonly array $response_values = [],
        public readonly ?string $message = null
    ) {
    }
}
