<?php

namespace Ridouchire\RadioDbImporter\Tracks\Track;

class Hash
{
    public static function fromPath(string $path): self
    {
        return new self(md5_file($path));
    }

    public static function fromString(string $hash): self
    {
        return new self($hash);
    }

    public function toString(): string
    {
        return $this->hash;
    }

    private function __construct(
        private string $hash
    ) {
    }
}
