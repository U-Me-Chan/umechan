<?php

namespace PK\Utils\MediaParsers;

use PK\Utils\IMediaParser;

final class YoutubeLinkParser implements IMediaParser
{
    private const REG_EXP_SHORT_LINK = '/((`{1,})[\s\S]+?(`{1,}))(*SKIP)(*F)|https?:\/\/youtu\.be\/([0-9a-z_-]+)(\?si\=([a-z0-9-_]+))?/mi';
    private const REG_EXP_LONG_LINK  = '/((`{1,})[\s\S]+?(`{1,}))(*SKIP)(*F)|https?:\/\/www\.youtube\.com\/watch\?v=([0-9a-z_-]+)/mi';

    public static function execute(string $message): array
    {
        $links = [];

        if (preg_match_all(self::REG_EXP_LONG_LINK, $message, $matches)) {
            foreach ($matches[4] as $id) {
                $links[$id] = [
                    'link' => "https://youtu.be/{$id}",
                    'preview' => "https://i1.ytimg.com/vi/{$id}/hqdefault.jpg"
                ];
            }
        }

        if (preg_match_all(self::REG_EXP_SHORT_LINK, $message, $matches)) {
            foreach ($matches[4] as $id) {
                $links[$id] = [
                    'link' => "https://youtu.be/{$id}",
                    'preview' => "https://i1.ytimg.com/vi/{$id}/hqdefault.jpg"
                ];
            }
        }

        $message = preg_replace(self::REG_EXP_LONG_LINK, '', $message);
        $message = preg_replace(self::REG_EXP_SHORT_LINK, '', $message);

        return [array_values($links), $message];
    }
}
