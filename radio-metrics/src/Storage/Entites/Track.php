<?php

namespace Ridouchire\RadioMetrics\Storage\Entites;

use Ridouchire\RadioMetrics\Storage\AEntity;

class Track extends AEntity
{
    public static function draft(string $track = ''): self
    {
        return new self(0, $track, time(), time(), 0, 0);
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

    public function bumpPlayCount(): void
    {
        $this->play_count = $this->play_count + 1;
    }

    public function togglePlaying(): void
    {
        $this->last_playing = time();
    }

    public function bumpEstimate(int $listeners): void
    {
        $this->estimate = $this->estimate + $listeners;
    }

    public function getName(): string
    {
        return $this->track;
    }

    public function setDuration(int $duration): void
    {
        $this->duration = $duration;
    }

    public function setPath(string $path): void
    {
        $this->path = $path;
    }

    public function setArtist(string $artist): void
    {
        $this->artist = $artist;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function setMpdTrackId(string $mpd_track_id): void
    {
        $this->mpd_track_id = $mpd_track_id;
    }

    private function __construct(
        private int $id,
        private string $track,
        private int $first_playing,
        private int $last_playing,
        private int $play_count,
        private int $estimate,
        private ?string $path = null,
        private ?int $duration = null,
        private ?string $artist = null,
        private ?string $title = null,
        private ?int $mpd_track_id = null
    ) {
    }
}
