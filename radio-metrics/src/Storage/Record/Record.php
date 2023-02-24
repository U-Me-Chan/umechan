<?php

namespace Ridouchire\RadioMetrics\Storage\Record;

use Ridouchire\RadioMetrics\Storage\AEntity;

class Record extends AEntity
{
    public static function draft(
        int $artist_id,
        int $track_id,
        int $playlist_id,
        int $listeners = 0
    ): self {
        return new self(0, $artist_id, $track_id, $playlist_id, $listeners, time());
    }

    public static function fromArray(array $state): self
    {
        return new self(
            $state['id'],
            $state['artist_id'],
            $state['track_id'],
            $state['playlist_id'],
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

    private function __construct(
        public int $id,
        public int $artist_id,
        public int $track_id,
        public int $playlist_id,
        public int $listeners,
        public int $timestamp
    ) {
    }
}
