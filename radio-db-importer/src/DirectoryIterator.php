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

    public function getFile(): SplFileInfo
    {
        try {
            /** @var SplFileInfo */
            $file = $this->iterator->current();
        } catch (RuntimeException) {
            $this->iterator->rewind();
            $file = $this->iterator->current();
        }

        return $file;
    }

    public function next(): void
    {
        $this->iterator->next();
    }
}
