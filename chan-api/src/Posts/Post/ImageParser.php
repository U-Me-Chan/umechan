<?php

namespace PK\Posts\Post;

use PK\Utils\PostMessageParser;

/**
 * @template TImageParseData of array{link: string, preview: string, type: 'image'}
 */
final class ImageParser extends PostMessageParser
{
    private const REGEXP = '/'.
        self::SKIP_CODE_BLOCK_REGEXP .
        '|' .
        '\[\!\[([a-z\W]+)?\]\((?<preview>' .
        self::BASE_URL .
        '\/files\/thumb\.\w+\.(jpe?g?|png|webp|jfif|gif\.jpeg))\)\]\((?<link>' .
        self::BASE_URL .
        '\/files\/\w+\.(jpe?g?|png|webp|jfif|gif))\)' .
        '/mi';


    /**
     * @return array{list<TImageParseData>, string}
     */
    public static function parse(string $message): array
    {
        $images = [];

        if (preg_match_all(self::REGEXP, $message, $matches)) {
            foreach ($matches['link'] as $k => $link) {
                $images[$link] = [
                    'link'    => $link,
                    'preview' => $matches['preview'][$k],
                    'type'    => 'image'
                ];
            }
        }

        $message = preg_replace(self::REGEXP, '',  $message);

        return [array_values($images), $message];
    }
}
