<?php

namespace Ridouchire\RadioScheduler;

interface IRotation
{
    public const NAME = 'strategy';

    public function execute(): void;
    public function isFired(): bool;
}
