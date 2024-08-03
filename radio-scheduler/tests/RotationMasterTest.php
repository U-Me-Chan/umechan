<?php

use Monolog\Logger;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Ridouchire\RadioScheduler\RotationMaster;
use Ridouchire\RadioScheduler\RotationStrategies\ByEstimateInGenre;
use Ridouchire\RadioScheduler\RotationStrategies\GenrePattern;
use Ridouchire\RadioScheduler\RotationStrategies\RandomTracksInGenrePattern;

class RotationMasterTest extends TestCase
{
    public function testWihtoutStrategies(): void
    {
        /** @var Logger|MockObject */
        $logger = $this->createMock(Logger::class);

        $rotation_master = new RotationMaster($logger);

        $this->expectException(\Exception::class);

        $rotation_master->execute('test');
    }

    public function testStrategyNotFound(): void
    {
        /** @var Logger|MockObject */
        $logger = $this->createMock(Logger::class);

        $rotation_master = new RotationMaster($logger);
        $rotation_master->addStrategy($this->createMock(ByEstimateInGenre::class));

        $this->expectException(\Exception::class);

        $rotation_master->execute('test');
    }

    public function testStrategyExecute(): void
    {
        /** @var Logger|MockObject */
        $logger = $this->createMock(Logger::class);
        $logger->method('info')->willReturnCallback(function (string $message) {
            $this->assertEquals('Текущая стратегия: ' . RandomTracksInGenrePattern::NAME, $message);
        });

        $rotation_master = new RotationMaster($logger);
        $rotation_master->addStrategy($this->createMock(RandomTracksInGenrePattern::class));

        $rotation_master->execute(RandomTracksInGenrePattern::NAME);

        $this->assertEquals(RandomTracksInGenrePattern::NAME, $rotation_master->getCurrentStrategy());
    }

    public function testGetRandomStrategy(): void
    {
        /** @var Logger|MockObject */
        $logger = $this->createMock(Logger::class);

        $rotation_master = new RotationMaster($logger);
        $rotation_master->addStrategy($this->createMock(RandomTracksInGenrePattern::class));
        $rotation_master->addStrategy($this->createMock(ByEstimateInGenre::class));

        $rotation_master->execute(RandomTracksInGenrePattern::NAME);

        $this->assertContains($rotation_master->getRandomStrategy(), [RandomTracksInGenrePattern::NAME, ByEstimateInGenre::NAME]);

        $rotation_master->execute($rotation_master->getRandomStrategy());

        $this->assertNotEquals(RandomTracksInGenrePattern::NAME, $rotation_master->getCurrentStrategy());
    }
}
