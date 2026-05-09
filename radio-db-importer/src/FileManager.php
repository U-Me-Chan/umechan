<?php

namespace Ridouchire\RadioDbImporter;

class FileManager
{
    public function __construct(
        private string $music_dir_of_convertible_files,
        private string $music_dir_of_files_without_tags,
        private string $music_dir_of_negative_estimate,
        private string $music_dir_path
    ) {
    }

    public function isFileExist(string $absolute_filepath): bool
    {
        return file_exists($absolute_filepath);
    }

    public function removeFile(string $absolute_filepath): bool
    {
        return unlink($absolute_filepath);
    }

    public function getRelativeFilepath(string $filepath): string
    {
        return str_replace($this->music_dir_path . DIRECTORY_SEPARATOR, '', $filepath);
    }

    public function getAbsoluteFilepath(string $relative_filepath): string
    {
        return $this->music_dir_path . DIRECTORY_SEPARATOR . $relative_filepath;
    }

    public function isNegativeDir(string $filepath): bool
    {
        $filepath = $this->getRelativeFilepath($filepath);

        $chunks = array_slice(explode(DIRECTORY_SEPARATOR, $filepath), 0, 1, true);

        return reset($chunks) == $this->getRelativeFilepath($this->music_dir_of_negative_estimate) ? true : false;
    }

    public function isDuplicateDir(string $filepath): bool
    {
        $filepath = $this->getRelativeFilepath($filepath);

        $chunks = array_slice(explode(DIRECTORY_SEPARATOR, $filepath), 0, 1, true);

        return reset($chunks) == 'Duplicate' ? true : false;
    }

    public function moveToDirOfConvertibleFiles(string $orig_path, string $filename): string
    {
        rename(
            $orig_path,
            $this->music_dir_of_convertible_files . DIRECTORY_SEPARATOR . $filename
        );

        return $this->music_dir_of_convertible_files . DIRECTORY_SEPARATOR . $filename;
    }

    public function moveToDirOfFilesWithoutTags(string $orig_path, string $filename): string
    {
        rename(
            $orig_path,
            $this->music_dir_of_files_without_tags . DIRECTORY_SEPARATOR . $filename
        );

        return $this->music_dir_of_files_without_tags . DIRECTORY_SEPARATOR . $filename;
    }

    public function moveToDirOfNegativeEstimate(string $orig_path, string $filename): string
    {
        rename(
            $orig_path,
            $this->music_dir_of_negative_estimate . DIRECTORY_SEPARATOR . $filename
        );

        return $this->music_dir_of_negative_estimate . DIRECTORY_SEPARATOR . $filename;
    }
}
