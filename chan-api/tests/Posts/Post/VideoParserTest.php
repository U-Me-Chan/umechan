<?php

use PHPUnit\Framework\TestCase;
use PK\Posts\Post\VideoParser;

class VideoParserTest extends TestCase
{
    public function test(): void
    {
        $orig_message = '[![](https://scheoble.xyz/files/thumb.test.mp4.jpeg)](https://scheoble.xyz/files/test.mp4)\n';
        $orig_message .= '\n[![](https://scheoble.xyz/files/thumb.test.webm.jpeg)](https://scheoble.xyz/files/test.webm)\n';

        list($videos, $message) = VideoParser::parse($orig_message);

        $this->assertEquals('\n\n\n', $message);
        $this->assertCount(2, $videos);

        $video_data = $videos[0];

        $this->assertArrayHasKey('link', $video_data);
        $this->assertEquals('https://scheoble.xyz/files/test.mp4', $video_data['link']);

        $this->assertArrayHasKey('preview', $video_data);
        $this->assertEquals('https://scheoble.xyz/files/thumb.test.mp4.jpeg', $video_data['preview']);

        $video_data = $videos[1];

        $this->assertArrayHasKey('link', $video_data);
        $this->assertEquals('https://scheoble.xyz/files/test.webm', $video_data['link']);

        $this->assertArrayHasKey('preview', $video_data);
        $this->assertEquals('https://scheoble.xyz/files/thumb.test.webm.jpeg', $video_data['preview']);
    }
}
