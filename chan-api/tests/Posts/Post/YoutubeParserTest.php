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
                'https://youtu.be/IJf6U07fb5A?si=sadkskadkask test',
                [
                    [
                        [
                            'link'    => 'https://youtu.be/IJf6U07fb5A',
                            'preview' => "https://i1.ytimg.com/vi/IJf6U07fb5A/hqdefault.jpg"
                        ]
                    ],
                    ' test'
                ]
            ],
            [
                'https://youtu.be/IJf6U07fb5A',
                [
                    [
                        [
                            'link'    => 'https://youtu.be/IJf6U07fb5A',
                            'preview' => "https://i1.ytimg.com/vi/IJf6U07fb5A/hqdefault.jpg"
                        ]
                    ],
                    ''
                ]
            ],
            [
                'https://www.youtube.com/watch?v=IJf6U07fb5A&si=sdcakeodkdk3003',
                [
                    [
                        [
                            'link'    => 'https://youtu.be/IJf6U07fb5A',
                            'preview' => 'https://i1.ytimg.com/vi/IJf6U07fb5A/hqdefault.jpg'
                        ]
                    ],
                    ''
                ]
            ],
            [
                'https://www.youtube.com/watch?v=IJf6U07fb5A',
                [
                    [
                        [
                            'link'    => 'https://youtu.be/IJf6U07fb5A',
                            'preview' => 'https://i1.ytimg.com/vi/IJf6U07fb5A/hqdefault.jpg'
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
                            'link'    => 'https://youtu.be/IJf6U07fb5A',
                            'preview' => 'https://i1.ytimg.com/vi/IJf6U07fb5A/hqdefault.jpg'
                        ]
                    ],
                    ''
                ]
            ],
            [
                'https://youtube.com/shorts/IJf6U07fb5A',
                [
                    [
                        [
                            'link'    => 'https://youtu.be/IJf6U07fb5A',
                            'preview' => 'https://i1.ytimg.com/vi/IJf6U07fb5A/hqdefault.jpg'
                        ]
                    ],
                    ''
                ]
            ]
        ];
    }
}
