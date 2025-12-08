<?php

namespace IH\Services\MimetypeExtractors;

use finfo;
use IH\Enums\Mimetype;
use IH\Services\MimetypeExtractor;

class FinfoMimetypeExtractor implements MimetypeExtractor
{
    private finfo $finfo;

    public function __construct()
    {
        $this->finfo = new finfo(FILEINFO_MIME);
    }

    public function extract(string $path_to_file): Mimetype
    {
        $mimetype = $this->finfo->file($path_to_file);
        $mimetype = explode(';', $mimetype);
        $mimetype = reset($mimetype);

        return match ($mimetype) {
            'image/jpeg'      => Mimetype::image,
            'image/png'       => Mimetype::image,
            'image/webp'      => Mimetype::image,
            'image/gif'       => Mimetype::video, // некоторые тамбнейлы с артефактами, если обрабатывать как изображение
            'video/webm'      => Mimetype::video,
            'video/mp4'       => Mimetype::video,
            'video/quicktime' => Mimetype::video,
            default           => Mimetype::unsupported
        };
    }
}
