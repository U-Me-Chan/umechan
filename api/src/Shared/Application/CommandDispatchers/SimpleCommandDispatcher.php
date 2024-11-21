<?php

namespace PK\Shared\Application;

use SplObjectStorage;
use PK\Shared\Infrastructrure\CommandExecuteStatus;
use PK\Shared\Infrastructrure\ICommand;
use PK\Shared\Infrastructrure\ICommandHandler;

class SimpleCommandDispatcher
{
    public function __construct(
        private SplObjectStorage $storage
    ) {
        $this->storage = new SplObjectStorage();
    }

    public function addCommandHandler(ICommand $command, ICommandHandler $handler): void
    {
        $this->storage->attach($command, $handler);
    }

    public function handle(ICommand $command): CommandExecuteStatus
    {
        try {
            $handler = $this->storage->offsetGet($command);
        } catch (\UnexpectedValueException) {
            throw new \RuntimeException();
        }

        return $handler($command);
    }
}
