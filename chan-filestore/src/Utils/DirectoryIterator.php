<?php

namespace IH\Utils;

use FilesystemIterator;
use GlobIterator;
use LimitIterator;
use SplFileInfo;

class DirectoryIterator
{
    private GlobIterator $iterator;

    public function __construct(
        string $pattern
    ) {
        $this->iterator = new GlobIterator(
            $pattern,
            FilesystemIterator::CURRENT_AS_FILEINFO | FilesystemIterator::SKIP_DOTS
        );
    }

    public function getSlice(int $offset, int $limit): array
    {
        $files = [];

        /** @var SplFileInfo  $file */
        foreach (new LimitIterator($this->iterator, $offset, $limit) as $file) {
            $files[] = $file;
        }

        return $files;
    }

    public function rewind(): void
    {
        $this->iterator->rewind();
    }

    public function count(): int
    {
        return $this->iterator->count();
    }

    public function current(): SplFileInfo
    {
        return $this->iterator->current();
    }

    public function next(): void
    {
        $this->iterator->next();
    }
}
