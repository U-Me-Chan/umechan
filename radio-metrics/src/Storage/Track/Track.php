<?php

namespace Ridouchire\RadioMetrics\Storage\Track;

use Ridouchire\RadioMetrics\Storage\AEntity;

class Track extends AEntity
{
    public static function draft(string $track = ''): self
    {
        return new self(0, $track, time(), time(), 1, 0);
    }

    public static function fromArray(array $state): self
    {
        return new self(
            $state['id'],
            $state['track'],
            $state['first_playing'],
            $state['last_playing'],
            $state['play_count'],
            $state['estimate']
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
        public string $track,
        public int $first_playing,
        public int $last_playing,
        public int $play_count,
        public int $estimate
    ) {
    }
}
