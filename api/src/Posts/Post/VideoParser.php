<?php

namespace PK\Posts\Post;

use PK\Utils\PostMessageParser;

final class VideoParser extends PostMessageParser
{
    private const REGEXP = '/' .
        self::SKIP_CODE_BLOCK_REGEXP .
        '|' .
        '\[\!\[\]\((?<preview>https\:\/\/scheoble\.xyz\/files\/thumb\.\w+\.(webm|mp4)\.jpeg)\)\]\((?<link>https\:\/\/scheoble\.xyz\/files\/\w+\.(webm|mp4))\)' .
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
