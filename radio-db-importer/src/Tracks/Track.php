<?php

namespace Ridouchire\RadioDbImporter\Tracks;

use InvalidArgumentException;
use Ridouchire\RadioDbImporter\Tracks\Track\Hash;

class Track
{
    private bool $is_updated = false;
    private bool $is_draft   = true;

    public function __construct(
        private string $artist,
        private string $title,
        private int $duration,
        private string $path,
        private Hash $hash,
        private int $estimate = 0,
        /** @phpstan-ignore property.onlyWritten */
        private int $first_playing = 0,
        /** @phpstan-ignore property.onlyWritten */
        private int $last_playing = 0,
        private int $play_count = 0,
        private int $id = 0
    ) {
        if ($id !== 0) {
            $this->is_draft = false;
        }
    }

    public static function fromArray(array $state): self
    {
        return new self(
            $state['artist'],
            $state['title'],
            $state['duration'],
            $state['path'],
            Hash::fromString($state['hash']),
            $state['estimate'],
            $state['first_playing'],
            $state['last_playing'],
            $state['play_count'],
            $state['id']
        );
    }

    public function toArray(): array
    {
        if ($this->is_draft) {
            return  [
                'estimate'      => 0,
                'first_playing' => time(),
                'last_playing'  => time(),
                'play_count'    => 0,
                'hash'          => $this->getHash(),
                'artist'        => $this->artist,
                'title'         => $this->title,
                'duration'      => $this->duration,
                'path'          => $this->path
            ];
        }

        return [
            'artist' => $this->artist,
            'title'  => $this->title,
            'path'   => $this->path,
        ];
    }

    public function __set(string $name, string|int $value)
    {
        if (!in_array($name, ['artist', 'title', 'path'])) {
            throw new InvalidArgumentException();
        }

        $this->$name = $value;

        $this->is_updated = true;
    }

    public function isUpdated(): bool
    {
        return $this->is_updated;
    }

    public function getHash(): string
    {
        return $this->hash->toString();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getArtist(): string
    {
        return $this->artist;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getEstimate(): int
    {
        return $this->estimate;
    }

    public function isBad(): bool
    {
        if ($this->play_count < 1) {
            return false;
        }

        if ($this->estimate < (0 - $this->duration * 5)) {
            return true;
        }

        return false;
    }
}
