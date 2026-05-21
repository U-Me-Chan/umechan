<?php

namespace IH\Services\Thumbnailers;

use IH\Enums\Filetype;
use IH\Services\Thumbnailer;

class ImageThumbnailer implements Thumbnailer
{
    private \Imagick $image;

    public static function getType(): Filetype
    {
        return Filetype::image;
    }

    public function __construct(
        private string $upload_dir_path
    ) {
        $this->image = new \Imagick();
    }

    public function readFromFile(string $filename): void
    {
        $fh = fopen($this->upload_dir_path . $filename, 'r');

        $this->image->readImageFile($fh);

        fclose($fh);
    }

    public function create(int $width, int $height): void
    {
        $this->image->scaleImage($width, $height, true);
    }

    public function save(string $filename): string
    {
        $thumbname = 'thumb' . '.' . $filename;

        $this->image->writeImage($this->upload_dir_path . $thumbname);

        return $thumbname;
    }
}
