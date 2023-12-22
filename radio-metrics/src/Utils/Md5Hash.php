<?php

namespace Ridouchire\RadioMetrics\Utils;

use RuntimeException;

class Md5Hash
{
    public function __construct(
        private string $mpd_database_path
    ) {
    }

    public function get(string $relative_path_file): string
    {
        if (!file_exists($this->mpd_database_path . '/' . $relative_path_file)) {
            throw new RuntimeException("Нет такого файла: {$this->mpd_database_path}/{$relative_path_file}");
        }

        return md5_file($this->mpd_database_path . '/' . $relative_path_file);
    }
}
