<?php

use Monolog\Logger;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Ridouchire\RadioScheduler\RotationMaster;
use Ridouchire\RadioScheduler\RotationStrategies\ByEstimateInGenre;
use Ridouchire\RadioScheduler\RotationStrategies\GenrePattern;

class RotationMasterTest extends TestCase
{
    private Logger|MockObject $logger;
    private GenrePattern|MockObject $genre_pattern_strategy;
    private ByEstimateInGenre|MockObject $by_estimate_in_genre_strategy;

    public function setUp(): void
    {
        $this->logger                        = $this->createMock(Logger::class);
        $this->genre_pattern_strategy        = $this->createMock(GenrePattern::class);
        $this->by_estimate_in_genre_strategy = $this->createMock(ByEstimateInGenre::class);
    }

    public function testWihtoutStrategies(): void
    {
        $rotation_master = new RotationMaster($this->logger);

        $this->expectException(\Exception::class);

        $rotation_master->execute('test');
    }

    public function testStrategyNotFound(): void
    {
        $rotation_master = new RotationMaster($this->logger);
        $rotation_master->addStrategy($this->by_estimate_in_genre_strategy);

        $this->expectException(\Exception::class);

        $rotation_master->execute('test');
    }

    public function testStrategyExecute(): void
    {
        $this->logger->method('info')->willReturnCallback(function (string $message) {
            $this->assertEquals('Текущая стратегия: ' . GenrePattern::NAME, $message);
        });

        $rotation_master = new RotationMaster($this->logger);
        $rotation_master->addStrategy($this->genre_pattern_strategy);
        $rotation_master->execute(GenrePattern::NAME);

        $this->assertEquals(GenrePattern::NAME, $rotation_master->getCurrentStrategy());
    }

    public function testGetRandomStrategy(): void
    {
        $rotation_master = new RotationMaster($this->logger);
        $rotation_master->addStrategy($this->genre_pattern_strategy);
        $rotation_master->addStrategy($this->by_estimate_in_genre_strategy);

        $rotation_master->execute(GenrePattern::NAME);

        $this->assertContains($rotation_master->getRandomStrategy(), [GenrePattern::NAME, ByEstimateInGenre::NAME]);

        $rotation_master->execute($rotation_master->getRandomStrategy());

        $this->assertNotEquals(GenrePattern::NAME, $rotation_master->getCurrentStrategy());
    }
}
