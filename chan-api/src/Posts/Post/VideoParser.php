<?php

namespace PK\Posts\Post;

use PK\Utils\PostMessageParser;

/**
 * @template TVideoParseData of array{link: string, preview: string, type: 'video'}
 */
final class VideoParser extends PostMessageParser
{
    private const REGEXP = '/' .
        self::SKIP_CODE_BLOCK_REGEXP .
        '|' .
        '\[\!\[([a-z\W]+)?\]\((?<preview>' .
        self::BASE_URL .
        '\/files\/thumb\.\w+\.(webm|mp4|mov)\.jpeg)\)\]\((?<link>' .
        self::BASE_URL .
        '\/files\/\w+\.(webm|mp4|mov))\)' .
        '/mi';

    /**
     * @return array{list<TVideoParseData>, string}
     */
    public static function parse(string $message): array
    {
        $videos = [];

        if (preg_match_all(self::REGEXP, $message, $matches)) {
            foreach ($matches['link'] as $k => $link) {
                $videos[$link] = [
                    'link'    => $link,
                    'preview' => $matches['preview'][$k],
                    'type'    => 'video'
                ];
            }
        }

        $message = preg_replace(self::REGEXP, '', $message);

        return [array_values($videos), $message];
    }
}
