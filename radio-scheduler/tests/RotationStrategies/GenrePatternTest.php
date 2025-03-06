<?php

use Medoo\Medoo;
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
    private Medoo|MockObject $db;
    private Mpd|MockObject $mpd;
    private Logger|MockObject $logger;
    private Jingles|MockObject $jingles;

    public function setUp(): void
    {
        $this->db      = $this->createMock(Medoo::class);
        $this->mpd     = $this->createMock(Mpd::class);
        $this->logger  = $this->createMock(Logger::class);
        $this->jingles = $this->createMock(Jingles::class);
    }

    public function testWithQuequeContainMoreThanOneTrack(): void
    {
        $this->mpd->expects($this->once())->method('getQueueCount')->willReturn(2);

        $this->logger->expects($this->once())->method('debug')->willReturnCallback(function (string $message) {
            $this->assertEquals('GenrePattern: очередь ещё не подошла к концу', $message);
        });

        $strategy = new GenrePattern($this->db, $this->mpd, $this->jingles, $this->logger);

        $strategy->execute();
    }

    #[DataProvider('dataProvider')]
    public function test(string $datetime, array $pls): void
    {
        $this->mpd->method('getQueueCount')->willReturn(1);

        $this->db->expects($this->atLeast(2))->method('rand')->willReturnCallback(function (string $table, string $field, array $conditions) use ($pls) {
            $this->assertEquals('tracks', $table);
            $this->assertEquals('path', $field);

            $this->assertArrayHasKey('path[~]', $conditions);
            $_path = explode('/', $conditions['path[~]']);

            if (sizeof($_path) == 3) {
                $path = "{$_path[0]}/{$_path[1]}";
            } else {
                $path = $_path[0];
            }

            $this->assertContains($path, $pls);

            $this->assertArrayHasKey('estimate[>=]', $conditions);
            $this->assertEquals(0, $conditions['estimate[>=]']);

            $this->assertArrayHasKey('LIMIT', $conditions);
            $this->assertEquals(0, $conditions['LIMIT'][0]);
            $this->assertContains($conditions['LIMIT'][1], range(5, 7));

            return [
                'Dir/1.mp3',
                'Dir/2.mp3',
                'Dir/3.mp3',
                'Dir/4.mp3',
                'Dir/5.mp3'
            ];
        });

        $this->jingles->expects($this->once())->method('getJingles')->willReturn(['Jingles/1.mp3']);

        $this->mpd->expects($this->atLeast(5))->method('addToQueue')->willReturnCallback(function (string $path) {
            $this->assertContains($path, ['Dir/1.mp3', 'Dir/2.mp3', 'Dir/3.mp3', 'Dir/4.mp3', 'Dir/5.mp3', 'Jingles/1.mp3']);

            return true;
        });

        $this->logger->expects($this->atLeast(6))->method('info');

        $strategy = new GenrePattern($this->db, $this->mpd, $this->jingles, $this->logger);

        $strategy->execute(strtotime($datetime));
    }

    public static function dataProvider(): array
    {
        return [
            ['2024-04-14 00:00:00', Night::getAll()],
            ['2024-04-14 01:00:00', Night::getAll()],
            ['2024-04-14 02:00:00', Night::getAll()],
            ['2024-04-14 03:00:00', Night::getAll()],
            ['2024-04-14 04:00:00', Night::getAll()],
            ['2024-04-14 05:00:00', Night::getAll()],
            ['2024-04-14 06:00:00', Morning::getAll()],
            ['2024-04-14 07:00:00', Morning::getAll()],
            ['2024-04-14 08:00:00', Morning::getAll()],
            ['2024-04-14 09:00:00', Day::getAll()],
            ['2024-04-14 10:00:00', Day::getAll()],
            ['2024-04-14 11:00:00', Day::getAll()],
            ['2024-04-14 12:00:00', Day::getAll()],
            ['2024-04-14 13:00:00', Day::getAll()],
            ['2024-04-14 14:00:00', Day::getAll()],
            ['2024-04-14 15:00:00', Day::getAll()],
            ['2024-04-14 16:00:00', Day::getAll()],
            ['2024-04-14 17:00:00', Day::getAll()],
            ['2024-04-14 18:00:00', Day::getAll()],
            ['2024-04-14 19:00:00', Evening::getAll()],
            ['2024-04-14 20:00:00', Evening::getAll()],
            ['2024-04-14 21:00:00', Evening::getAll()],
            ['2024-04-14 22:00:00', Evening::getAll()],
            ['2024-04-14 23:00:00', Evening::getAll()],
        ];
    }
}
