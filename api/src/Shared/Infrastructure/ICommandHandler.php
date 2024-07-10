<?php

namespace PK\Shared\Infrastructrure;

interface ICommandHandler
{
    public function execute(ICommand $command): CommandExecuteStatus;
}
