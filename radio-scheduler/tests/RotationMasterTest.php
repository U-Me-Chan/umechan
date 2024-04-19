<?php

use Monolog\Logger;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Ridouchire\RadioScheduler\RotationMaster;
use Ridouchire\RadioScheduler\RotationStrategies\AverageInGenre;
use Ridouchire\RadioScheduler\RotationStrategies\TopInGenre;

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
        $rotation_master->addStrategy($this->createMock(TopInGenre::class));

        $this->expectException(\Exception::class);

        $rotation_master->execute('test');
    }

    public function testStrategyExecute(): void
    {
        /** @var Logger|MockObject */
        $logger = $this->createMock(Logger::class);
        $logger->method('info')->willReturnCallback(function (string $message) {
            $this->assertEquals('Текущая стратегия: ' . TopInGenre::NAME, $message);
        });

        $rotation_master = new RotationMaster($logger);
        $rotation_master->addStrategy($this->createMock(TopInGenre::class));

        $rotation_master->execute(TopInGenre::NAME);

        $this->assertEquals(TopInGenre::NAME, $rotation_master->getCurrentStrategy());
    }

    public function testGetRandomStrategy(): void
    {
        /** @var Logger|MockObject */
        $logger = $this->createMock(Logger::class);

        $rotation_master = new RotationMaster($logger);
        $rotation_master->addStrategy($this->createMock(TopInGenre::class));
        $rotation_master->addStrategy($this->createMock(AverageInGenre::class));

        $rotation_master->execute(TopInGenre::NAME);

        $this->assertContains($rotation_master->getRandomStrategy(), [TopInGenre::NAME, AverageInGenre::NAME]);

        $rotation_master->execute($rotation_master->getRandomStrategy());

        $this->assertNotEquals(TopInGenre::NAME, $rotation_master->getCurrentStrategy());
    }
}
