<?php

namespace Ridouchire\RadioDbImporter;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RuntimeException;
use SplFileInfo;

class DirectoryIterator
{
    private RecursiveIteratorIterator $iterator;

    public function __construct(
        private string $dir_path
    ) {
        $this->iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(
                $this->dir_path,
                RecursiveDirectoryIterator::SKIP_DOTS
            ),
            RecursiveIteratorIterator::SELF_FIRST
        );
    }

    /**
     * @throws RuntimeException
     */
    public function getFile(): SplFileInfo
    {
        return $this->iterator->current();
    }

    public function next(): void
    {
        $this->iterator->next();
    }
}
