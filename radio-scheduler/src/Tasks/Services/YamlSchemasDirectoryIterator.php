<?php

namespace Ridouchire\RadioScheduler\Tasks\Services;

use Generator;
use InvalidArgumentException;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RuntimeException;
use SplFileInfo;

class YamlSchemasDirectoryIterator
{
    public function __construct(
        private string $dir,
        private string $pattern = '/\.(yaml|yml)$/i'
    ) {
    }

    public function getIterator(): Generator
    {
        if (!is_dir($this->dir)) {
            throw new InvalidArgumentException($this->dir . ' не является директорией');
        }

        if (!is_readable($this->dir)) {
            throw new RuntimeException($this->dir . ' недоступна для чтения');
        }

        $iterator = new RecursiveDirectoryIterator($this->dir, RecursiveDirectoryIterator::SKIP_DOTS);
        $recursiveIterator = new RecursiveIteratorIterator($iterator);

        foreach ($recursiveIterator as $file) {
            if ($file instanceof SplFileInfo && $file->isFile()) {
                if (preg_match($this->pattern, $file->getPathname())) {
                    yield realpath($file->getPathname());
                }
            }
        }
    }
}
