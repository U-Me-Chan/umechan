<?php

use PHPUnit\Framework\TestCase;
use PK\Posts\Post\YoutubeParser;

class YoutubeParserTest extends TestCase
{
    /**
     * @dataProvider dpForTestParse
     */
    public function testParse(string $message, array $expected): void
    {
        $this->assertEquals($expected, YoutubeParser::parse($message));
    }

    public function dpForTestParse(): array
    {
        return [
            [
                'https://youtu.be/sdd22s',
                [
                    [
                        [
                            'link'    => 'https://youtu.be/sdd22s',
                            'preview' => "https://i1.ytimg.com/vi/sdd22s/hqdefault.jpg"
                        ]
                    ],
                    ''
                ]
            ],
            [
                'https://www.youtube.com/watch?v=sdd22s',
                [
                    [
                        [
                            'link'    => 'https://youtu.be/sdd22s',
                            'preview' => 'https://i1.ytimg.com/vi/sdd22s/hqdefault.jpg'
                        ]
                    ],
                    ''
                ]
            ]
        ];
    }
}
