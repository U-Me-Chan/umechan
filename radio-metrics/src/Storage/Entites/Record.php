<?php

namespace Ridouchire\RadioMetrics\Storage\Entites;

use Ridouchire\RadioMetrics\Storage\AEntity;

class Record extends AEntity
{
    public static function draft(
        int $track_id,
        int $listeners = 0
    ): self {
        return new self(0, $track_id, $listeners, time());
    }

    public static function fromArray(array $state): self
    {
        return new self(
            $state['id'],
            $state['track_id'],
            $state['listeners'],
            $state['timestamp']
        );
    }

    public function toArray(): array
    {
        return get_object_vars($this);
    }

    public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getTrackId(): int
    {
        return $this->track_id;
    }

    public function getListeners(): int
    {
        return $this->listeners;
    }

    private function __construct(
        private int $id,
        private int $track_id,
        private int $listeners,
        private int $timestamp
    ) {
    }
}
