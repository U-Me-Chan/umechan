<?php

use Medoo\Medoo;
use Medoo\Raw;
use Monolog\Logger;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Ridouchire\RadioScheduler\GenreSchemas\Day;
use Ridouchire\RadioScheduler\GenreSchemas\Evening;
use Ridouchire\RadioScheduler\GenreSchemas\Morning;
use Ridouchire\RadioScheduler\GenreSchemas\Night;
use Ridouchire\RadioScheduler\Mpd;
use Ridouchire\RadioScheduler\RotationStrategies\AverageInGenre;

class AverageInGenreTest extends TestCase
{
    public function test(): void
    {
        /** @var Medoo|MockObject */
        $db = $this->createMock(Medoo::class);
        $db->method('select')->willReturnCallback(function (...$args) {
            $this->assertEquals('tracks', $args[0]);

            $this->assertEquals('path', $args[1]);
            $this->assertArrayHasKey('path[~]', $args[2]);
            $this->assertMatchesRegularExpression('/^[\w\s]+\/%$/', $args[2]['path[~]']);
            preg_match_all("/^(?'genre'[\w\s]+)\/%$/", $args[2]['path[~]'], $matches);
            $this->assertArrayHasKey('genre', $matches);
            $genre = $matches['genre'][0];

            $this->assertArrayHasKey('ORDER', $args[2]);
            $this->assertArrayHasKey('last_playing', $args[2]['ORDER']);
            $this->assertEquals('ASC', $args[2]['ORDER']['last_playing']);

            $this->assertArrayHasKey('estimate[>=]', $args[2]);
            $this->assertInstanceOf(Raw::class, $args[2]['estimate[>=]']);
            /** @var Raw */
            $raw_query = $args[2]['estimate[>=]'];
            $this->assertEquals("(SELECT AVG(estimate) FROM tracks WHERE path LIKE '{$genre}/%')", $raw_query->value);

            $this->assertArrayHasKey('LIMIT', $args[2]);
            $this->assertEquals(0, $args[2]['LIMIT'][0]);
            $this->assertEquals(10, $args[2]['LIMIT'][1]);

            return [
                'Pop/1.mp3',
                'Pop/2.mp3',
                'Pop/3.mp3',
                'Pop/4.mp3'
            ];
        });

        /** @var Mpd|MockObject */
        $mpd = $this->createMock(Mpd::class);

        /** @var Logger|MockObject */
        $logger = $this->createMock(Logger::class);

        $strategy = new AverageInGenre($db, $mpd, $logger);

        $strategy->execute();
    }

    public function testWithQueueIsBusy(): void
    {
        /** @var Mpd|MockObject */
        $mpd = $this->createMock(Mpd::class);
        $mpd->method('getQueueCount')->willReturn(2);

        /** @var Logger|MockObject */
        $logger = $this->createMock(Logger::class);
        $logger->method('debug')->willReturnCallback(function (string $message) {
            $this->assertEquals(AverageInGenre::NAME . ': очередь ещё не подошла к концу', $message);
        });

        $db = $this->createMock(Medoo::class);

        $strategy = new AverageInGenre($db, $mpd, $logger);

        $strategy->execute();
    }

    #[DataProvider('getNightDatetimes')]
    public function testNight(string $datetime): void
    {
        /** @var Mpd|MockObject */
        $mpd = $this->createMock(Mpd::class);
        $mpd->method('getQueueCount')->willReturn(1);
        $mpd->method('addToQueue')->willReturn(true);

        /** @var Logger|MockObject */
        $logger = $this->createMock(Logger::class);

        /** @var Medoo|MockObject */
        $db = $this->createMock(Medoo::class);
        $db->method('select')->willReturnCallback(function (...$args) {
            preg_match_all("/^(?'genre'[\w\s]+)\/%$/", $args[2]['path[~]'], $matches);
            $this->assertArrayHasKey('genre', $matches);
            $genre = $matches['genre'][0];

            $this->assertContains($genre, Night::getAll());

            return [
                'Pop/1.mp3',
                'Pop/2.mp3',
                'Pop/3.mp3',
                'Pop/4.mp3'
            ];
        });

        $strategy = new AverageInGenre($db, $mpd, $logger);

        $strategy->execute(strtotime($datetime));
    }

    #[DataProvider('getMorningDatetimes')]
    public function testMorning(string $datetime): void
    {
        /** @var Mpd|MockObject */
        $mpd = $this->createMock(Mpd::class);
        $mpd->method('getQueueCount')->willReturn(1);
        $mpd->method('addToQueue')->willReturn(true);

        /** @var Logger|MockObject */
        $logger = $this->createMock(Logger::class);

        /** @var Medoo|MockObject */
        $db = $this->createMock(Medoo::class);
        $db->method('select')->willReturnCallback(function (...$args) {
            preg_match_all("/^(?'genre'[\w\s]+)\/%$/", $args[2]['path[~]'], $matches);
            $this->assertArrayHasKey('genre', $matches);
            $genre = $matches['genre'][0];

            $this->assertContains($genre, Morning::getAll());

            return [
                'Pop/1.mp3',
                'Pop/2.mp3',
                'Pop/3.mp3',
                'Pop/4.mp3'
            ];
        });

        $strategy = new AverageInGenre($db, $mpd, $logger);

        $strategy->execute(strtotime($datetime));
    }

    #[DataProvider('getDayDatetimes')]
    public function testDay(string $datetime): void
    {
        /** @var Mpd|MockObject */
        $mpd = $this->createMock(Mpd::class);
        $mpd->method('getQueueCount')->willReturn(1);
        $mpd->method('addToQueue')->willReturn(true);

        /** @var Logger|MockObject */
        $logger = $this->createMock(Logger::class);

        /** @var Medoo|MockObject */
        $db = $this->createMock(Medoo::class);
        $db->method('select')->willReturnCallback(function (...$args) {
            preg_match_all("/^(?'genre'[\w\s]+)\/%$/", $args[2]['path[~]'], $matches);
            $this->assertArrayHasKey('genre', $matches);
            $genre = $matches['genre'][0];

            $this->assertContains($genre, Day::getAll());

            return [
                'Pop/1.mp3',
                'Pop/2.mp3',
                'Pop/3.mp3',
                'Pop/4.mp3'
            ];
        });

        $strategy = new AverageInGenre($db, $mpd, $logger);

        $strategy->execute(strtotime($datetime));
    }

    #[DataProvider('getEveningDatetimes')]
    public function testEvening(string $datetime): void
    {
        /** @var Mpd|MockObject */
        $mpd = $this->createMock(Mpd::class);
        $mpd->method('getQueueCount')->willReturn(1);
        $mpd->method('addToQueue')->willReturn(true);

        /** @var Logger|MockObject */
        $logger = $this->createMock(Logger::class);

        /** @var Medoo|MockObject */
        $db = $this->createMock(Medoo::class);
        $db->method('select')->willReturnCallback(function (...$args) {
            preg_match_all("/^(?'genre'[\w\s]+)\/%$/", $args[2]['path[~]'], $matches);
            $this->assertArrayHasKey('genre', $matches);
            $genre = $matches['genre'][0];

            $this->assertContains($genre, Evening::getAll());

            return [
                'Pop/1.mp3',
                'Pop/2.mp3',
                'Pop/3.mp3',
                'Pop/4.mp3'
            ];
        });

        $strategy = new AverageInGenre($db, $mpd, $logger);

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
