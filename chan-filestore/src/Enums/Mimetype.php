<?php

namespace IH\Enums;

enum Mimetype:string
{
    case image_bmp   = 'image/bmp';
    case image_jpeg  = 'image/jpeg';
    case image_pjpeg = 'image/pjpeg';
    case image_tiff  = 'image/tiff';
    case image_png   = 'image/png';
    case image_webp  = 'image/webp';
    case image_gif   = 'image/gif';

    case video_webm  = 'video/webm';
    case video_mp4   = 'video/mp4';
    case video_mov   = 'video/quicktime';

    public static function getAll(): array
    {
        return array_map(fn(Mimetype $mimetype) => $mimetype->value, self::cases());
    }
}
