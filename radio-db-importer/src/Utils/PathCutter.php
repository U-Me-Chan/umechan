<?php

namespace Ridouchire\RadioDbImporter\Utils;

final class PathCutter
{
    public function __construct(
        private string $music_dir_path
    ) {
    }

    public function cut(string $path): string
    {
        return str_replace($this->music_dir_path . '/', '', $path);
    }
}
