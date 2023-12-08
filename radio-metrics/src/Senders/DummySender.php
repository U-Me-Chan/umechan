<?php

namespace Ridouchire\RadioMetrics\Senders;

use Ridouchire\RadioMetrics\ISender;
use Ridouchire\RadioMetrics\Storage\Entites\Track;

class DummySender implements ISender
{
    public function send(Track $track, int $listeners, string $additional = ''): void
    {
    }

    public function getName(): string
    {
        return 'DummySender';
    }
}
