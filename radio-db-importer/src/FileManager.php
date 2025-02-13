<?php

namespace Ridouchire\RadioDbImporter;

class FileManager
{
    public function __construct(
        private string $music_dir_of_convertible_files,
        private string $music_dir_of_files_without_tags
    ) {
    }

    public function moveToDirOfConvertibleFiles(string $orig_path, string $filename): void
    {
        rename(
            $orig_path,
            $this->music_dir_of_convertible_files . DIRECTORY_SEPARATOR . $filename
        );
    }

    public function moveToDirOfFilesWithoutTags(string $orig_path, string $filename): void
    {
        rename(
            $orig_path,
            $this->music_dir_of_files_without_tags . DIRECTORY_SEPARATOR . $filename
        );
    }
}
