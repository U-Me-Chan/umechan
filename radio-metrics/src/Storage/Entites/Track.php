<?php

namespace Ridouchire\RadioMetrics\Storage\Entites;

use Ridouchire\RadioMetrics\Storage\AEntity;

class Track extends AEntity
{
    public static function draft(
        string $artist,
        string $title,
        string $hash,
        string $path,
        int $duration = 0,
        int $mpd_track_id = 0
    ): self {
        return new self(0, $artist, $title, time(), time(), 0, 0, $path, $duration, $mpd_track_id, $hash);
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
            $state['mpd_track_id'],
            $state['hash']
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

    public function setHash(string $hash): void
    {
        $this->hash = $hash;
    }

    public function bumpPlayCount(): void
    {
        $this->play_count = $this->play_count + 1;
    }

    public function increaseEstimate(int $value): void
    {
        $this->estimate = $this->estimate + $value;
    }

    public function setEstimate(int $estimate): void
    {
        $this->estimate = $estimate;
    }

    public function decreaseEstimate(): void
    {
        $estimate = (int) ceil($this->estimate / $this->duration);

        if ($estimate < 0)  {
            $this->estimate = ceil($this->estimate * 2); // @phpstan-ignore assign.propertyType
        } else if ($estimate == 0 || $estimate == 1 || $estimate == 2) {
            $this->estimate = $this->estimate - ($this->duration * 3);
        } else {
            $this->estimate = ceil($this->estimate / 2); // @phpstan-ignore assign.propertyType
        }
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

    public function getHash(): string|null
    {
        return $this->hash;
    }

    public function getEstimate(): int
    {
        return $this->estimate;
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
        private ?int $mpd_track_id = null,
        private ?string $hash = null
    ) {
    }
}
