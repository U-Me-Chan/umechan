<?php

namespace IH\Services\Thumbnailers;

use IH\Enums\Mimetype;
use IH\Services\Thumbnailer;

class VideoThumbnailer implements Thumbnailer
{
    private const THUMBNAIL_FILE_FORMAT = 'jpeg';
    private const THUMBNAIL_QUALITY = '9';

    private string $filepath;
    private string $temp_thumb_filepath;

    public static function getType(): Mimetype
    {
        return Mimetype::video;
    }

    public function __construct(
        private string $upload_dir_path
    ) {
    }

    public function readFromFile(string $filepath): void
    {
        $this->filepath = $filepath;
    }

    public function create(int $width, int $height): void
    {
        $this->temp_thumb_filepath = tempnam(sys_get_temp_dir(), 'filestore_webm_');

        $shell = 'ffmpegthumbnailer' .
            ' -s ' . max($width, $height) .
            ' -c ' . self::THUMBNAIL_FILE_FORMAT .
            ' -q ' . self::THUMBNAIL_QUALITY .
            ' -i ' . $this->filepath .
            ' -o ' . $this->temp_thumb_filepath .
            ' 2>/dev/null';
        exec($shell);

        list($w, $h) = getimagesize($this->temp_thumb_filepath);

        if ($w == 0 || $h == 0) {
            unlink($this->temp_thumb_filepath);

            throw new \Exception;
        }

        $imagick = new \Imagick();
        $draw    = new \ImagickDraw();
        $pixel   = new \ImagickPixel();

        $fh = fopen($this->temp_thumb_filepath, 'r');

        $imagick->readImageFile($fh);

        fclose($fh);

        $draw->setFillColor('white');
        $draw->setFontSize(50);
        $imagick->annotateImage($draw, 50, 50, 0, 'Video File');
        $imagick->writeImage($this->temp_thumb_filepath);
    }

    public function save(string $filename): string
    {
        $thumbname = 'thumb' . '.' . $filename . '.' . self::THUMBNAIL_FILE_FORMAT;

        rename(
            $this->temp_thumb_filepath,
            $this->upload_dir_path . $thumbname
        );

        chmod($this->upload_dir_path . $thumbname, 0777);

        return $thumbname;
    }
}
