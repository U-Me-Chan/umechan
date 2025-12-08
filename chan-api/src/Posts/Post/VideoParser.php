<?php

namespace PK\Posts\Post;

use PK\Utils\PostMessageParser;

final class VideoParser extends PostMessageParser
{
    private const REGEXP = '/' .
        self::SKIP_CODE_BLOCK_REGEXP .
        '|' .
        '\[\!\[([a-z\W]+)?\]\((?<preview>' .
        self::BASE_URL .
        '\/files\/thumb\.\w+\.(webm|mp4|gif|mov)\.jpeg)\)\]\((?<link>' .
        self::BASE_URL .
        '\/files\/\w+\.(webm|mp4|gif|mov))\)' .
        '/mi';

    public static function parse(string $message): array
    {
        $videos = [];

        if (preg_match_all(self::REGEXP, $message, $matches)) {
            foreach ($matches['link'] as $k => $link) {
                $videos[$link] = [
                    'link'    => $link,
                    'preview' => $matches['preview'][$k]
                ];
            }
        }

        $message = preg_replace(self::REGEXP, '', $message);

        return [array_values($videos), $message];
    }
}
