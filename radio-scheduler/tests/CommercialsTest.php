<?php

use Medoo\Medoo;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Ridouchire\RadioScheduler\Commercials;

class CommercialsTest extends TestCase
{
    private Medoo|MockObject $db;
    private Commercials|MockObject $commercials;

    public function setUp(): void
    {
        $this->db          = $this->createMock(Medoo::class);
        $this->commercials = new Commercials($this->db);
    }

    public function testGetCommercials(): void
    {
        $this->db->method('rand')->willReturnCallback(function (...$args) {
            $this->assertEquals('tracks', $args[0]);
            $this->assertEquals('path', $args[1]);
            $this->assertArrayHasKey('path[~]', $args[2]);
            $this->assertEquals('Commercials/%', $args[2]['path[~]']);
            $this->assertArrayHasKey('LIMIT', $args[2]);
            $this->assertEquals(0, $args[2]['LIMIT'][0]);
            $this->assertEquals(2, $args[2]['LIMIT'][1]);

            return [
                'Tests/1.mp3',
                'Tests/2.mp3'
            ];
        });

        list($first_file, $second_file) = $this->commercials->getCommercials(2);

        $this->assertEquals('Tests/1.mp3', $first_file);
        $this->assertEquals('Tests/2.mp3', $second_file);
    }
}
