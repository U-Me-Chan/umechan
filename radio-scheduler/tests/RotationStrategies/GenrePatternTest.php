<?php

use Monolog\Logger;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Ridouchire\RadioScheduler\GenreSchemas\Day;
use Ridouchire\RadioScheduler\GenreSchemas\Evening;
use Ridouchire\RadioScheduler\GenreSchemas\Morning;
use Ridouchire\RadioScheduler\GenreSchemas\Night;
use Ridouchire\RadioScheduler\Jingles;
use Ridouchire\RadioScheduler\Mpd;
use Ridouchire\RadioScheduler\RotationStrategies\GenrePattern;

class GenrePatternTest extends TestCase
{
    public function testWithQuequeContainMoreThanOneTrack(): void
    {
        /** @var Mpd|MockObject */
        $mpd = $this->createMock(Mpd::class);
        $mpd->method('getQueueCount')->willReturn(2);

        /** @var Logger|MockObject */
        $logger = $this->createMock(Logger::class);
        $logger->method('debug')->willReturnCallback(function (...$args) {
            $this->assertEquals('GenrePatternStrategy: очередь ещё не подошла к концу', $args[0]);
        });

        /** @var Jingles|MockObject */
        $jingles = $this->createMock(Jingles::class);
        $jingles->method('getJingles')->willReturn([
            'Jingles/1.mp3',
            'Jingles/2.mp3'
        ]);

        $strategy = new GenrePattern($mpd, $jingles, $logger);

        $strategy->execute();
    }

    #[DataProvider('getNightDatetimes')]
    public function testNight(string $datetime): void
    {
        /** @var Mpd|MockObject */
        $mpd = $this->createMock(Mpd::class);
        $mpd->method('getQueueCount')->willReturn(1);
        $mpd->method('getCountSongsInDirectory')->willReturnCallback(function (...$args) {
            $this->assertContains($args[0], Night::getAll());

            return 10;
        });
        $mpd->method('getTracks')->willReturnCallback(function (...$args) {
            $this->assertContains($args[0], Night::getAll());

            $this->assertLessThanOrEqual(10, $args[1]);
            $this->assertEquals($args[1] + 1, $args[2]);

            return [
                [
                    'file' => '1.mp3'
                ],
                [
                    'file' => '2.mp3'
                ]
            ];
        });
        $mpd->method('addToQueue')->willReturnCallback(function (...$args) {
            $this->assertContains($args[0], ['1.mp3', '2.mp3', 'Jingles/1.mp3', 'Jingles/2.mp3']);

            return true;
        });

        /** @var Logger|MockObject */
        $logger = $this->createMock(Logger::class);

        /** @var Jingles|MockObject */
        $jingles = $this->createMock(Jingles::class);
        $jingles->method('getJingles')->willReturn([
            'Jingles/1.mp3',
            'Jingles/2.mp3'
        ]);

        $strategy = new GenrePattern($mpd, $jingles, $logger);

        $strategy->execute(strtotime($datetime));
    }

    public static function getNightDatetimes(): array
    {
        return [
            ['2024-04-14 00:00:00'],
            ['2024-04-14 01:00:00'],
            ['2024-04-14 02:00:00'],
            ['2024-04-14 03:00:00'],
            ['2024-04-14 04:00:00'],
            ['2024-04-14 05:00:00']
        ];
    }


    #[DataProvider('getMorningDatetimes')]
    public function testMorning(string $datetime): void
    {
        /** @var Mpd|MockObject */
        $mpd = $this->createMock(Mpd::class);
        $mpd->method('getQueueCount')->willReturn(1);
        $mpd->method('getCountSongsInDirectory')->willReturnCallback(function (...$args) {
            $this->assertContains($args[0], Morning::getAll());

            return 10;
        });
        $mpd->method('getTracks')->willReturnCallback(function (...$args) {
            $this->assertContains($args[0], Morning::getAll());

            $this->assertLessThanOrEqual(10, $args[1]);
            $this->assertEquals($args[1] + 1, $args[2]);

            return [
                [
                    'file' => '1.mp3'
                ],
                [
                    'file' => '2.mp3'
                ]
            ];
        });
        $mpd->method('addToQueue')->willReturnCallback(function (...$args) {
            $this->assertContains($args[0], ['1.mp3', '2.mp3', 'Jingles/1.mp3', 'Jingles/2.mp3']);

            return true;
        });

        /** @var Logger|MockObject */
        $logger = $this->createMock(Logger::class);

        /** @var Jingles|MockObject */
        $jingles = $this->createMock(Jingles::class);
        $jingles->method('getJingles')->willReturn([
            'Jingles/1.mp3',
            'Jingles/2.mp3'
        ]);

        $strategy = new GenrePattern($mpd, $jingles, $logger);

        $strategy->execute(strtotime($datetime));
    }

    public static function getMorningDatetimes(): array
    {
        return [
            ['2024-04-14 06:00:00'],
            ['2024-04-14 07:00:00'],
            ['2024-04-14 08:00:00']
        ];
    }

    #[DataProvider('getDayDatetimes')]
    public function testDay(string $datetime): void
    {
        /** @var Mpd|MockObject */
        $mpd = $this->createMock(Mpd::class);
        $mpd->method('getQueueCount')->willReturn(1);
        $mpd->method('getCountSongsInDirectory')->willReturnCallback(function (...$args) {
            $this->assertContains($args[0], Day::getAll());

            return 10;
        });
        $mpd->method('getTracks')->willReturnCallback(function (...$args) {
            $this->assertContains($args[0], Day::getAll());

            $this->assertLessThanOrEqual(10, $args[1]);
            $this->assertEquals($args[1] + 1, $args[2]);

            return [
                [
                    'file' => '1.mp3'
                ],
                [
                    'file' => '2.mp3'
                ]
            ];
        });
        $mpd->method('addToQueue')->willReturnCallback(function (...$args) {
            $this->assertContains($args[0], ['1.mp3', '2.mp3', 'Jingles/1.mp3', 'Jingles/2.mp3']);

            return true;
        });

        /** @var Logger|MockObject */
        $logger = $this->createMock(Logger::class);

        /** @var Jingles|MockObject */
        $jingles = $this->createMock(Jingles::class);
        $jingles->method('getJingles')->willReturn([
            'Jingles/1.mp3',
            'Jingles/2.mp3'
        ]);

        $strategy = new GenrePattern($mpd, $jingles, $logger);

        $strategy->execute(strtotime($datetime));
    }

    public static function getDayDatetimes(): array
    {
        return [
            ['2024-04-14 09:00:00 '],
            ['2024-04-14 10:00:00'],
            ['2024-04-14 11:00:00'],
            ['2024-04-14 12:00:00'],
            ['2024-04-14 13:00:00'],
            ['2024-04-14 14:00:00'],
            ['2024-04-14 15:00:00'],
            ['2024-04-14 16:00:00'],
            ['2024-04-14 17:00:00'],
            ['2024-04-14 18:00:00'],
        ];
    }

    #[DataProvider('getEveningDatetimes')]
    public function testEvening(string $datetime): void
    {
        /** @var Mpd|MockObject */
        $mpd = $this->createMock(Mpd::class);
        $mpd->method('getQueueCount')->willReturn(1);
        $mpd->method('getCountSongsInDirectory')->willReturnCallback(function (...$args) {
            $this->assertContains($args[0], Evening::getAll());

            return 10;
        });
        $mpd->method('getTracks')->willReturnCallback(function (...$args) {
            $this->assertContains($args[0], Evening::getAll());

            $this->assertLessThanOrEqual(10, $args[1]);
            $this->assertEquals($args[1] + 1, $args[2]);

            return [
                [
                    'file' => '1.mp3'
                ],
                [
                    'file' => '2.mp3'
                ]
            ];
        });
        $mpd->method('addToQueue')->willReturnCallback(function (...$args) {
            $this->assertContains($args[0], ['1.mp3', '2.mp3', 'Jingles/1.mp3', 'Jingles/2.mp3']);

            return true;
        });

        /** @var Logger|MockObject */
        $logger = $this->createMock(Logger::class);

        /** @var Jingles|MockObject */
        $jingles = $this->createMock(Jingles::class);
        $jingles->method('getJingles')->willReturn([
            'Jingles/1.mp3',
            'Jingles/2.mp3'
        ]);

        $strategy = new GenrePattern($mpd, $jingles, $logger);

        $strategy->execute(strtotime($datetime));
    }

    public static function getEveningDatetimes(): array
    {
        return [
            ['2024-04-14 19:00:00 '],
            ['2024-04-14 20:00:00'],
            ['2024-04-14 21:00:00'],
            ['2024-04-14 22:00:00'],
            ['2024-04-14 23:00:00'],
        ];
    }
}
