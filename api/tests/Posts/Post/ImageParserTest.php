<?php

use PHPUnit\Framework\TestCase;
use PK\Posts\Post\ImageParser;

class ImageParserTest extends TestCase
{
    /**
     * @dataProvider dataProvider
     */
    public function test(
        string $orig_message,
        string $expected_message,
        array $expected_images = []
    ): void
    {
        list($images, $message) = ImageParser::parse($orig_message);

        $this->assertEquals($expected_message, $message);
        $this->assertEquals($expected_images, $images);
    }

    public function dataProvider(): array
    {
        return [
            [
                '[![](https://scheoble.xyz/files/thumb.test.jpeg)](https://scheoble.xyz/files/test.jpeg)',
                '',
                [
                    [
                        'preview' => 'https://scheoble.xyz/files/thumb.test.jpeg',
                        'link'    => 'https://scheoble.xyz/files/test.jpeg'
                    ]
                ]
            ],
            [
                '[![](https://scheoble.xyz/files/thumb.test.jpe)](https://scheoble.xyz/files/test.jpe)',
                '',
                [
                    [
                        'preview' => 'https://scheoble.xyz/files/thumb.test.jpe',
                        'link'    => 'https://scheoble.xyz/files/test.jpe'
                    ]
                ]
            ],
            [
                '[![](https://scheoble.xyz/files/thumb.test.jpg)](https://scheoble.xyz/files/test.jpg)',
                '',
                [
                    [
                        'preview' => 'https://scheoble.xyz/files/thumb.test.jpg',
                        'link'    => 'https://scheoble.xyz/files/test.jpg'
                    ]
                ]
            ],
            [
                '[![](https://scheoble.xyz/files/thumb.test.png)](https://scheoble.xyz/files/test.png)',
                '',
                [
                    [
                        'preview' => 'https://scheoble.xyz/files/thumb.test.png',
                        'link'    => 'https://scheoble.xyz/files/test.png'
                    ]
                ]
            ],
            [
                '[![](https://scheoble.xyz/files/thumb.test.gif)](https://scheoble.xyz/files/test.gif)',
                '',
                [
                    [
                        'preview' => 'https://scheoble.xyz/files/thumb.test.gif',
                        'link'    => 'https://scheoble.xyz/files/test.gif'
                    ]
                ]
            ]
        ];
    }
}
