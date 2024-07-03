<?php

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Ridouchire\RadioScheduler\Mpd;
use Ridouchire\RadioScheduler\QueueCropper;

class QueueCropperTest extends TestCase
{

    #[DataProvider('getTimestamps')]
    public function testPositive(int $timestamp): void
    {
        /** @var Mpd|MockObject */
        $mpd = $this->createMock(Mpd::class);
        $mpd->method('cropQueue')->willReturn(true);
        $queue_cropper = new QueueCropper($mpd);

        $this->assertTrue($queue_cropper($timestamp));
    }

    public static function getTimestamps(): array
    {
        return [
            [1719964800],
            [1719986400],
            [1719997200],
            [1720033200]
        ];
    }

    public function testNegative(): void
    {
        /** @var Mpd|MockObject */
        $mpd = $this->createMock(Mpd::class);
        $mpd->method('cropQueue')->willReturn(true);
        $queue_cropper = new QueueCropper($mpd);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Ещё не время');

        $queue_cropper(1720035240);
    }
}
