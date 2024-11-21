<?php

namespace PK\Utils\MediaParsers;

use PK\Utils\IMediaParser;

final class ImageLinkParser implements IMediaParser
{
    private const REG_EXP_MARKDOWN_LINK = '/((`{1,})[\s\S]+?(`{1,}))(*SKIP)(*F)|\[\!\[\]\((?<preview>.+)\)\]\((?<link>.+)\)/mi';
    private const REG_EXP_GENERAL_LINK = '/((`{1,})[\s\S]+?(`{1,}))(*SKIP)(*F)|https?\:\/\/[a-z0-9_\-.\/]+\.(jpe?g?|gif|png)(\?[a-z0-9=_\/\-&]+)?/mi';
    private const REG_EXP_TWITTER_LINK = '/((`{1,})[\s\S]+?(`{1,}))(*SKIP)(*F)|https?\:\/\/[a-z0-9_\-.\/]+\.(jpe?g?|gif|png)(\?[a-z0-9=_\/\-&]+)?/mi';

    public static function execute(string $message): array
    {
        $images = [];

        if (preg_match_all(self::REG_EXP_MARKDOWN_LINK, $message, $matches)) {
            foreach ($matches['link'] as $k => $link) {
                $images[$link] = [
                    'link' => $link,
                    'preview' => $matches['preview'][$k]
                ];
            }
        }

        if (preg_match_all(self::REG_EXP_GENERAL_LINK, $message, $matches)) {
            foreach ($matches[0] as $link) {
                $images[$link] = [
                    'link' => $link,
                    'preview' => $link
                ];
            }
        }

        if (preg_match_all(self::REG_EXP_TWITTER_LINK, $message, $matches)) {
            foreach ($matches[0] as $link) {
                $images[$link] = [
                    'link' => $link,
                    'preview' => $link
                ];
            }
        }

        $message = preg_replace(self::REG_EXP_MARKDOWN_LINK, '', $message);
        $message = preg_replace(self::REG_EXP_GENERAL_LINK, '',  $message);
        $message = preg_replace(self::REG_EXP_TWITTER_LINK, '', $message);

        return [array_values($images), $message];
    }
}
