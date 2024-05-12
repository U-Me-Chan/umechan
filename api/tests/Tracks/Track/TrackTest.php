<?php

use PHPUnit\Framework\TestCase;
use PK\Base\Timestamp;
use PK\Tracks\Track\Track;

class TrackTest extends TestCase
{
    /**
     * @dataProvider dpGetProperty
     */
    public function testGetProperty(string $prop, mixed $value): void
    {
        $track = Track::draft('foo', 'bar', 'spam');

        switch($prop) {
            case 'first_playing':
                $this->assertInstanceOf(Timestamp::class, $track->first_playing);
                $this->assertNotEquals($value, $track->first_playing->toInt());

                break;
            case 'last_playing':
                $this->assertInstanceOf(Timestamp::class, $track->last_playing);
                $this->assertNotEquals($value, $track->last_playing->toInt());

                break;
            default:
                $this->assertEquals($value, $track->$prop);
        }
    }

    public function dpGetProperty(): array
    {
        return [
            ['artist', 'foo'],
            ['title', 'bar'],
            ['path', 'spam'],
            ['first_playing', 0],
            ['last_playing', 0],
            ['duration', 0],
            ['play_count', 0],
            ['estimate', 0],
            ['hash', null]
        ];
    }

    public function testGetInvalidArgumentProperty(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Неизвестное свойство: test');

        $track = Track::draft('foo', 'bar', 'spam');

        $track->test;
    }

    /**
     * @dataProvider dpSetProperty
     */
    public function testSetProperty(string $prop, mixed $value): void
    {
        $track = Track::draft('foo', 'bar', 'spam');
        $track->$prop = $value;

        switch ($prop) {
            case 'first_playing':
                $this->assertInstanceOf(Timestamp::class, $track->first_playing);
                $this->assertNotEquals(0, $track->first_playing->toInt());

                break;
            case 'last_playing':
                $this->assertInstanceOf(Timestamp::class, $track->last_playing);
                $this->assertNotEquals(0, $track->last_playing->toInt());

                break;
            default:
                $this->assertEquals($value, $track->$prop);
        }
    }

    public function dpSetProperty(): array
    {
        return [
            ['artist', 'kewk'],
            ['title', 'blah'],
            ['path', 'chick'],
            ['first_playing', time()],
            ['last_playing', time()],
            ['duration', 120],
            ['play_count', 10],
            ['estimate', 200],
            ['hash', 'hash']
        ];
    }

    /**
     * @dataProvider dpSetInvalidProperty
     */
    public function testSetInvalidProperty(string $prop, string $exception_message): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage($exception_message);

        $track = Track::draft('foo', 'bar', 'spam');

        $track->$prop = 'test';
    }

    public function dpSetInvalidProperty(): array
    {
        return [
            ['id', 'Нельзя задать идентификатор композиции'],
            ['test', 'Неизвестное свойство: test']
        ];
    }
}
