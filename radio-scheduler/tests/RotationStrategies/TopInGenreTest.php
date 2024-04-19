<?php

use Medoo\Medoo;
use Medoo\Raw;
use Monolog\Logger;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Ridouchire\RadioScheduler\Mpd;
use Ridouchire\RadioScheduler\RotationStrategies\TopInGenre;

class TopInGenreTest extends TestCase
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
            $this->assertEquals("SELECT AVG(estimate) FROM tracks WHERE path LIKE '{$genre}/%'", $raw_query->value);

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

        $strategy = new TopInGenre($db, $mpd, $logger);

        $strategy->execute();
    }
}
