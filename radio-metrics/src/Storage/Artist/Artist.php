<?php

namespace Ridouchire\RadioMetrics\Storage\Artist;

use Ridouchire\RadioMetrics\Storage\AEntity;

class Artist extends AEntity
{
    public static function draft(string $artist = ''): self
    {
        return new self(0, $artist, time(), time(), 0, 0);
    }

    public static function fromArray(array $state): self
    {
        return new self(
            $state['id'],
            $state['artist'],
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
        public string $artist,
        public int $first_playing,
        public int $last_playing,
        public int $play_count,
        public int $estimate
    ) {
    }
}
