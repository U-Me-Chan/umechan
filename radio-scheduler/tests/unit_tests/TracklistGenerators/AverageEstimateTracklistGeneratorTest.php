<?php

use Medoo\Medoo;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Ridouchire\RadioScheduler\IRandomizer;
use Ridouchire\RadioScheduler\TracklistGenerators\AverageEstimateTracklistGenerator;

class AverageEstimateTracklistGeneratorTest extends TestCase
{
    private AverageEstimateTracklistGenerator $generator;
    private MockObject|IRandomizer $randomizer;
    private MockObject|Medoo $db;

    public function setUp(): void
    {
        $this->randomizer = $this->createMock(IRandomizer::class);

        $this->db = $this->createMock(Medoo::class);

        $this->generator = new AverageEstimateTracklistGenerator($this->db, $this->randomizer);
    }

    #[Test]
    public function attempRunWithEmptyGenreList(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->generator->build();
    }

    #[Test]
    public function attemptRunWithGenreInListIsEmpty(): void
    {
        $this->randomizer->method('getInt')->willReturnCallback(
            function (int $min, int $max) {
                $this->assertEquals(4, $min);
                $this->assertEquals(8, $max);

                return 4;
            }
        );

        $this->db->method('select')->willReturnCallback(
            function (string $table, string $field, array $conditions) {
                $this->assertEquals('tracks', $table);
                $this->assertEquals('path', $field);

                $this->assertArrayHasKey('path[~]', $conditions);
                $this->assertEquals('test/%', $conditions['path[~]']);

                $this->assertArrayHasKey('estimate[>=]', $conditions);
                $this->assertEquals(
                    "(SELECT AVG(estimate) FROM tracks WHERE path LIKE 'test/%')",
                    $conditions['estimate[>=]']->value
                );

                $this->assertArrayHasKey('ORDER', $conditions);
                $this->assertEquals(['last_playing' => 'ASC'], $conditions['ORDER']);

                $this->assertArrayHasKey('LIMIT', $conditions);
                $this->assertEquals(4, $conditions['LIMIT']);

                return null;
            }
        );

        $this->assertEmpty($this->generator->build(['test']));
    }

    #[Test]
    public function attemptRunWithTwoGenre(): void
    {
        $this->randomizer->method('getInt')->willReturn(4);
        $this->db->method('select')->willReturn(['one'], ['two', 'three'], null);

        $list = $this->generator->build(['foo', 'bar', 'spam']);

        $this->assertContains('one', $list);
        $this->assertContains('two', $list);
        $this->assertContains('three', $list);

        $this->assertCount(3, $list);
    }
}
