<?php

namespace PK\Posts\Post;

use PK\Utils\PostMessageParser;

final class YoutubeParser extends PostMessageParser
{
    private const SHORT_REGEXP = '/'.
        '((`{1,})[\s\S]+?(`{1,}))(*SKIP)(*F)' .
        '|' .
        'https?:\/\/youtu\.be\/([0-9a-z_-]+)(\?si\=([a-z0-9-_]+))?'.
        '/mi';

    private const LONG_REGEXP = '/'.
        '((`{1,})[\s\S]+?(`{1,}))(*SKIP)(*F)'.
        '|'.
        'https?:\/\/www\.youtube\.com\/watch\?v=([0-9a-z_-]+)'.
        '/mi';

    public static function parse(string $message): array
    {
        $youtubes = [];

        if (preg_match_all(self::LONG_REGEXP, $message, $matches)) {
            foreach ($matches[4] as $id) {
                $youtubes[$id] = [
                    'link'    => "https://youtu.be/{$id}",
                    'preview' => "https://i1.ytimg.com/vi/{$id}/hqdefault.jpg"
                ];
            }
        }

        if (preg_match_all(self::SHORT_REGEXP, $message, $matches)) {
            foreach ($matches[4] as $id) {
                $youtubes[$id] = [
                    'link'    => "https://youtu.be/{$id}",
                    'preview' => "https://i1.ytimg.com/vi/{$id}/hqdefault.jpg"
                ];
            }
        }

        $message = preg_replace(self::LONG_REGEXP, '', $message);
        $message = preg_replace(self::SHORT_REGEXP, '', $message);

        return [array_values($youtubes), $message];
    }
}
