<?php

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use PK\Posts\Post\ImageParser;

define('BASE_URL', 'https:\/\/scheoble.xyz');

class ImageParserTest extends TestCase
{
    #[DataProvider('dataProvider')]
    #[Test]
    public function test(
        string $orig_message,
        string $expected_message,
        array $expected_images = []
    ): void
    {
        list($images, $message) = ImageParser::parse($orig_message);

        $this->assertEquals($expected_message, $message, 'Сообщение не очищено');
        $this->assertEquals($expected_images, $images, 'Список изображений не соответствует ожидаемому');
    }

    public static function dataProvider(): array
    {
        return [
            [
                '[![](https://scheoble.xyz/files/thumb.test.jpeg)](https://scheoble.xyz/files/test.jpeg)',
                '',
                [
                    [
                        'preview' => 'https://scheoble.xyz/files/thumb.test.jpeg',
                        'link'    => 'https://scheoble.xyz/files/test.jpeg',
                        'type'    => 'image'
                    ]
                ]
            ],
            [
                '[![](https://scheoble.xyz/files/thumb.test.jpe)](https://scheoble.xyz/files/test.jpe)',
                '',
                [
                    [
                        'preview' => 'https://scheoble.xyz/files/thumb.test.jpe',
                        'link'    => 'https://scheoble.xyz/files/test.jpe',
                        'type'    => 'image'
                    ]
                ]
            ],
            [
                '[![](https://scheoble.xyz/files/thumb.test.jpg)](https://scheoble.xyz/files/test.jpg)',
                '',
                [
                    [
                        'preview' => 'https://scheoble.xyz/files/thumb.test.jpg',
                        'link'    => 'https://scheoble.xyz/files/test.jpg',
                        'type'    => 'image'
                    ]
                ]
            ],
            [
                '[![](https://scheoble.xyz/files/thumb.test.png)](https://scheoble.xyz/files/test.png)',
                '',
                [
                    [
                        'preview' => 'https://scheoble.xyz/files/thumb.test.png',
                        'link'    => 'https://scheoble.xyz/files/test.png',
                        'type'    => 'image'
                    ]
                ]
            ],
            [
                '[![](https://scheoble.xyz/files/thumb.test.jfif)](https://scheoble.xyz/files/test.jfif)',
                '',
                [
                    [
                        'preview' => 'https://scheoble.xyz/files/thumb.test.jfif',
                        'link'    => 'https://scheoble.xyz/files/test.jfif',
                        'type'    => 'image'
                    ]
                ]
            ],
            [
                '[![test.jf?s.if](https://scheoble.xyz/files/thumb.test.jfif)](https://scheoble.xyz/files/test.jfif)',
                '',
                [
                    [
                        'preview' => 'https://scheoble.xyz/files/thumb.test.jfif',
                        'link'    => 'https://scheoble.xyz/files/test.jfif',
                        'type'    => 'image'
                    ]
                ]
            ],
            [
                '[![](https://scheoble.xyz/files/thumb.test.gif.jpeg)](https://scheoble.xyz/files/test.gif)',
                '',
                [
                    [
                        'preview' => 'https://scheoble.xyz/files/thumb.test.gif.jpeg',
                        'link'    => 'https://scheoble.xyz/files/test.gif',
                        'type'    => 'image'
                    ]
                ]
            ]
        ];
    }
}
