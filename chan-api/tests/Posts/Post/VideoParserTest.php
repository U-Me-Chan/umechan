<?php

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use PK\Posts\Post\VideoParser;

class VideoParserTest extends TestCase
{
    #[Test]
    #[DataProvider('dataProvider')]
    public function test(
        string $orig_message,
        string $exp_message,
        array $exp_videos
    ): void
    {
        list($videos, $message) = VideoParser::parse($orig_message);

        $this->assertEquals($exp_message, $message);
        $this->assertEquals($exp_videos, $videos);
    }

    public static function dataProvider(): array
    {
        return [
            [
                '[![](https://scheoble.xyz/files/thumb.test.webm.jpeg)](https://scheoble.xyz/files/test.webm)',
                '',
                [
                    [
                        'preview' => 'https://scheoble.xyz/files/thumb.test.webm.jpeg',
                        'link'    => 'https://scheoble.xyz/files/test.webm',
                        'type'    => 'video'
                    ]
                ]
            ],
            [
                '[![](https://scheoble.xyz/files/thumb.test.mov.jpeg)](https://scheoble.xyz/files/test.mov)',
                '',
                [
                    [
                        'preview' => 'https://scheoble.xyz/files/thumb.test.mov.jpeg',
                        'link'    => 'https://scheoble.xyz/files/test.mov',
                        'type'    => 'video'
                    ]
                ]
            ],
            [
                '[![](https://scheoble.xyz/files/thumb.test.mp4.jpeg)](https://scheoble.xyz/files/test.mp4)',
                '',
                [
                    [
                        'preview' => 'https://scheoble.xyz/files/thumb.test.mp4.jpeg',
                        'link'    => 'https://scheoble.xyz/files/test.mp4',
                        'type'    => 'video'
                    ]
                ]
            ]
        ];
    }
}
