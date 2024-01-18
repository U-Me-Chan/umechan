<?php

use PHPUnit\Framework\TestCase;
use PK\Tracks\Track\Track;

class TrackTest extends TestCase
{
    public function testGetProperty(): void
    {
        $track = Track::draft('foo', 'bar', 'spam');
        $this->assertEquals('foo', $track->artist);

        $this->expectException(\InvalidArgumentException::class);
        $track->test;
    }

    public function testSetProperty(): void
    {
        $track = Track::draft('foo', 'bar', 'spam');
        $track->artist = 'test';
        $this->assertEquals('test', $track->artist);

        $this->expectException(\InvalidArgumentException::class);
        $track->test = 'test';
    }
}
