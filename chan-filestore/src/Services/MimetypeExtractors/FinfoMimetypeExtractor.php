<?php

namespace IH\Services\MimetypeExtractors;

use finfo;
use IH\Enums\Filetype;
use IH\Enums\Mimetype;
use IH\Services\MimetypeExtractor;

class FinfoMimetypeExtractor implements MimetypeExtractor
{
    private finfo $finfo;

    public function __construct()
    {
        $this->finfo = new finfo(FILEINFO_MIME);
    }

    public function extract(string $path_to_file): Filetype
    {
        $mimetype = $this->finfo->file($path_to_file);
        $mimetype = explode(';', $mimetype);
        $mimetype = reset($mimetype);

        return match ($mimetype) {
            Mimetype::image_bmp->value   => Filetype::image,
            Mimetype::image_jpeg->value  => Filetype::image,
            Mimetype::image_pjpeg->value => Filetype::image,
            Mimetype::image_tiff->value  => Filetype::image,
            Mimetype::image_png->value   => Filetype::image,
            Mimetype::image_webp->value  => Filetype::image,
            Mimetype::image_gif->value   => Filetype::video, // некоторые тамбнейлы с артефактами, если обрабатывать как изображение
            Mimetype::video_webm->value  => Filetype::video,
            Mimetype::video_mp4->value   => Filetype::video,
            Mimetype::video_mov->value   => Filetype::video,
            default               => Filetype::unsupported
        };
    }
}
