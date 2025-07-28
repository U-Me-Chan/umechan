<?php

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use PK\Posts\Post\YoutubeParser;

class YoutubeParserTest extends TestCase
{
    #[Test]
    #[DataProvider('dpForTestParse')]
    public function testParse(string $message, array $expected): void
    {
        $this->assertEquals($expected, YoutubeParser::parse($message));
    }

    public static function dpForTestParse(): array
    {
        return [
            [
                'https://youtu.be/short_link?si=sadkskadkask',
                [
                    [
                        [
                            'link'    => 'https://youtu.be/short_link',
                            'preview' => "https://i1.ytimg.com/vi/short_link/hqdefault.jpg"
                        ]
                    ],
                    ''
                ]
            ],
            [
                'https://www.youtube.com/watch?v=long_link&si=sdcakeodkdk3003',
                [
                    [
                        [
                            'link'    => 'https://youtu.be/long_link',
                            'preview' => 'https://i1.ytimg.com/vi/long_link/hqdefault.jpg'
                        ]
                    ],
                    ''
                ]
            ],
            [
                'https://youtube.com/shorts/IJf6U07fb5A?si=blahblah',
                [
                    [
                        [
                            'link' => 'https://youtube.com/shorts/IJf6U07fb5A',
                            'preview' => 'https://i.ytimg.com/vi/IJf6U07fb5A/maxres2.jpg'
                        ]
                    ],
                    ''
                ]
            ]
        ];
    }
}
