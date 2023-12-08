<?php

namespace Ridouchire\RadioMetrics\Storage\Entites;

use Ridouchire\RadioMetrics\Storage\AEntity;

class Track extends AEntity
{
    public static function draft(
        string $artist,
        string $title,
        int $duration = 0,
        string $path = '',
        int $mpd_track_id = 0
    ): self {
        return new self(0, $artist, $title, time(), time(), 0, 0, $path, $duration, $mpd_track_id);
    }

    public static function fromArray(array $state): self
    {
        return new self(
            $state['id'],
            $state['artist'],
            $state['title'],
            $state['first_playing'],
            $state['last_playing'],
            $state['play_count'],
            $state['estimate'],
            $state['path'],
            $state['duration'],
            $state['mpd_track_id']
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

    public function bumpEstimate(int $listeners): void
    {
        $this->estimate = $this->estimate + $listeners;
    }

    public function togglePlaying(): void
    {
        $this->last_playing = time();
    }

    public function getName(): string
    {
        return "{$this->artist} - {$this->title}";
    }

    public function getArtist(): string
    {
        return $this->artist;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getMpdTrackId(): int
    {
        return $this->mpd_track_id;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getDuration(): int
    {
        return $this->duration;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getPlayCount(): int
    {
        return $this->play_count;
    }

    private function __construct(
        private int $id,
        private string $artist,
        private string $title,
        private int $first_playing,
        private int $last_playing,
        private int $play_count,
        private int $estimate,
        private ?string $path = null,
        private ?int $duration = null,
        private ?int $mpd_track_id = null
    ) {
    }
}
