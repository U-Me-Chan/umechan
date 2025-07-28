<?php

namespace PK\Posts\Post;

use PK\Utils\PostMessageParser;

final class YoutubeParser extends PostMessageParser
{
    private const SOCIAL_TRACKER_MARK_REGEXP = 'si=([a-z0-9_-]+)';

    private const IDENTIFY_MEDIA_REGEXP = '[0-9a-z_-]+';

    private const SHORT_REGEXP = '/' .
        self::SKIP_CODE_BLOCK_REGEXP .
        '|' .
        'https?:\/\/youtu\.be\/(' .
        self::IDENTIFY_MEDIA_REGEXP .
        ')(\?' .
        self::SOCIAL_TRACKER_MARK_REGEXP .
        ')'.
        '/mi';

    private const LONG_REGEXP = '/' .
        self::SKIP_CODE_BLOCK_REGEXP .
        '|' .
        'https?:\/\/www\.youtube\.com\/watch\?v=(' .
        self::IDENTIFY_MEDIA_REGEXP .
        ')&' .
        self::SOCIAL_TRACKER_MARK_REGEXP .
        '/mi';

    private const REELS_REGEXP = '/' .
        self::SKIP_CODE_BLOCK_REGEXP .
        '|' .
        'https?:\/\/youtube\.com\/shorts\/(' .
        self::IDENTIFY_MEDIA_REGEXP .
        ')\?' .
        self::SOCIAL_TRACKER_MARK_REGEXP .
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

        if (preg_match_all(self::REELS_REGEXP, $message, $matches)) {
            foreach ($matches[4] as $id) {
                $youtubes[$id] = [
                    'link'    => "https://youtube.com/shorts/{$id}",
                    'preview' => "https://i.ytimg.com/vi/{$id}/maxres2.jpg"
                ];
            }
        }

        $message = preg_replace(self::LONG_REGEXP, '', $message);
        $message = preg_replace(self::SHORT_REGEXP, '', $message);
        $message = preg_replace(self::REELS_REGEXP, '', $message);

        return [array_values($youtubes), $message];
    }
}
