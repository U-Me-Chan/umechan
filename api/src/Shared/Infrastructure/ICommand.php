<?php

namespace PK\Shared\Infrastructrure;

interface ICommand
{
    public function toArray(): array;
}
