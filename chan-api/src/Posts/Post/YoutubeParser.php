<?php

namespace PK\Posts\Post;

use PK\Utils\PostMessageParser;

final class YoutubeParser extends PostMessageParser
{
    private const YOUTUBE_LINK_REGEXP = '/' .
        self::SKIP_CODE_BLOCK_REGEXP .
        '|' . 'https?\:\/\/(?:www\.|m\.)?youtu(?:\.)?be(?:\.com)?\/(?:[\w\?\&=\/\-]{1,})?/mi';

    private const ID_MEDIA_REGEXP = '/' .
        self::SKIP_CODE_BLOCK_REGEXP .
        '|' . '(?:.+?)?(?:\/v\/|watch\/|\?v=|\&v=|youtu\.be\/|\/v=|^youtu\.be\/|watch\%3Fv\%3D|shorts\/)([a-zA-Z0-9_-]{11})+/mi';

    public static function parse(string $message): array
    {
        $youtubes = [];

        if (preg_match_all(self::ID_MEDIA_REGEXP, $message, $matches)) {
            foreach ($matches[4] as $id) {
                $youtubes[$id] = [
                    'link'    => "https://youtu.be/{$id}",
                    'preview' => "https://i1.ytimg.com/vi/{$id}/hqdefault.jpg"
                ];
            }
        }

        $message = preg_replace(self::YOUTUBE_LINK_REGEXP, '', $message);

        return [array_values($youtubes), $message];
    }
}
