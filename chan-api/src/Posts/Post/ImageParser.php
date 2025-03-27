<?php

namespace PK\Posts\Post;

use PK\Utils\PostMessageParser;

final class ImageParser extends PostMessageParser
{
    private const REGEXP = '/'.
        self::SKIP_CODE_BLOCK_REGEXP .
        '|' .
        '\[\!\[\]\((?<preview>' .
        self::BASE_URL .
        '\/files\/thumb\.\w+\.(jpe?g?|png|webp|jfif))\)\]\((?<link>' .
        self::BASE_URL .
        '\/files\/\w+\.(jpe?g?|png|webp|jfif))\)' .
        '/mi';

    public static function parse(string $message): array
    {
        $images = [];

        if (preg_match_all(self::REGEXP, $message, $matches)) {
            foreach ($matches['link'] as $k => $link) {
                $images[$link] = [
                    'link'    => $link,
                    'preview' => $matches['preview'][$k]
                ];
            }
        }

        $message = preg_replace(self::REGEXP, '',  $message);

        return [array_values($images), $message];
    }
}
