<?php

namespace Ridouchire\RadioMetrics;

use Ridouchire\RadioMetrics\Storage\Entites\Track;

interface ISender
{
    public function send(Track $track, int $listeners, string $additional = ''): void;

    public function getName(): string;
}
