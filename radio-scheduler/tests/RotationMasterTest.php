<?php

use Monolog\Logger;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Ridouchire\RadioScheduler\RotationMaster;
use Ridouchire\RadioScheduler\RotationStrategies\ByEstimateInGenre;
use Ridouchire\RadioScheduler\RotationStrategies\GenrePattern;

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
        $rotation_master->addStrategy($this->createMock(GenrePattern::class));

        $this->expectException(\Exception::class);

        $rotation_master->execute('test');
    }

    public function testStrategyExecute(): void
    {
        /** @var Logger|MockObject */
        $logger = $this->createMock(Logger::class);
        $logger->method('info')->willReturnCallback(function (string $message) {
            $this->assertEquals('Текущая стратегия: ' . GenrePattern::NAME, $message);
        });

        $rotation_master = new RotationMaster($logger);
        $rotation_master->addStrategy($this->createMock(GenrePattern::class));

        $rotation_master->execute(GenrePattern::NAME);

        $this->assertEquals(GenrePattern::NAME, $rotation_master->getCurrentStrategy());
    }

    public function testGetRandomStrategy(): void
    {
        /** @var Logger|MockObject */
        $logger = $this->createMock(Logger::class);

        $rotation_master = new RotationMaster($logger);
        $rotation_master->addStrategy($this->createMock(GenrePattern::class));
        $rotation_master->addStrategy($this->createMock(ByEstimateInGenre::class));

        $rotation_master->execute(GenrePattern::NAME);

        $this->assertContains($rotation_master->getRandomStrategy(), [GenrePattern::NAME, ByEstimateInGenre::NAME]);

        $rotation_master->execute($rotation_master->getRandomStrategy());

        $this->assertNotEquals(GenrePattern::NAME, $rotation_master->getCurrentStrategy());
    }
}
