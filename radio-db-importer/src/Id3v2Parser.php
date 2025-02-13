<?php

namespace Ridouchire\RadioDbImporter;

use getID3;
use InvalidArgumentException;

class Id3v2Parser
{
    private array $data = [];

    public function __construct(
        private getID3 $parser
    ) {
    }

    public function readFile(string $path): void
    {
        $this->data = $this->parser->analyze($path);

        if (!isset($this->data['tags'])) {
            throw new InvalidArgumentException();
        }

        if (!isset($this->data['tags']['id3v2'])) {
            throw new InvalidArgumentException();
        }

        if (!isset($this->data['tags']['id3v2']['artist'])) {
            throw new InvalidArgumentException();
        }

        if (!isset($this->data['tags']['id3v2']['title'])) {
            throw new InvalidArgumentException();
        }
    }

    public function getArtist(): string
    {
        return $this->data['tags']['id3v2']['artist'][0];
    }

    public function getTitle(): string
    {
        return $this->data['tags']['id3v2']['title'][0];
    }

    public function getDuration(): int
    {
        return $this->data['playtime_seconds'];
    }
}
