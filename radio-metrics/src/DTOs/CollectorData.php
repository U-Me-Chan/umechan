<?php

namespace Ridouchire\RadioMetrics\DTOs;

class CollectorData
{
    public function __construct(
        private string $track,
        private int $listeners
    ) {
    }

    public function getTrack(): string
    {
        return $this->track;
    }

    public function getListeners(): int
    {
        return $this->listeners;
    }
}
