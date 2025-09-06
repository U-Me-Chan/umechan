<?php

use Monolog\Logger;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Ridouchire\RadioScheduler\IRotation;
use Ridouchire\RadioScheduler\RotationMaster;

class RotationMasterTest extends TestCase
{
    private Logger|MockObject $logger;
    private RotationMaster $rotation_master;

    public function setUp(): void
    {
        /** @var Logger|MockObject */
        $this->logger = $this->createMock(Logger::class);

        $this->rotation_master = new RotationMaster($this->logger);
    }

    #[Test]
    public function attemptExecuteWithoutRotations(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Нет стратегии ротации на данный час');

        $this->rotation_master->execute();
    }

    #[Test]
    public function attempExecuteWithStrategyIsNotFired(): void
    {
        /** @var IRotation|MockObject */
        $strategy = $this->createMock(IRotation::class);
        $strategy->method('isFired')->willReturn(false);

        $this->rotation_master->addStrategyByPeriod(0, 23, $strategy);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Нет стратегии ротации на данный час');

        $this->rotation_master->execute();
    }

    #[Test]
    public function attemptExecuteWithStrategyIsFired(): void
    {
        /** @var IRotation|MockObject */
        $strategy = $this->createMock(IRotation::class);
        $strategy->method('isFired')->willReturn(true);

        $this->rotation_master->addStrategyByPeriod(0, 23, $strategy);

        $this->logger->method('info')->willReturnCallback(function (string $message) {
            $this->assertEquals('RotationMaster: была запущена стратегия strategy', $message);
        });
        $this->logger->expects($this->once())->method('info');

        $this->rotation_master->execute();
    }

    #[Test]
    public function attempAddStrategyByPeriodWithInvalidArguments(): void
    {
        /** @var IRotation|MockObject */
        $strategy = $this->createMock(IRotation::class);

        $this->expectException(InvalidArgumentException::class);

        $this->rotation_master->addStrategyByPeriod(0, 0, $strategy);
        $this->rotation_master->addStrategyByPeriod(10, 5, $strategy);
        $this->rotation_master->addStrategyByPeriod(0, 100, $strategy);
    }

    #[Test]
    public function attempAddStrategyByHourWithInvalidArgument(): void
    {
        /** @var IRotation|MockObject */
        $strategy = $this->createMock(IRotation::class);

        $this->expectException(InvalidArgumentException::class);

        $this->rotation_master->addStrategyByHour(25, $strategy);
        $this->rotation_master->addStrategyByHour(-1, $strategy);
    }

    #[Test]
    public function attemptExecuteBySpecificHour(): void
    {
        /** @var IRotation|MockObject */
        $strategy = $this->createMock(IRotation::class);
        $strategy->method('isFired')->willReturn(true);

        $this->rotation_master->addStrategyByHour(0, $strategy);

        $this->logger->method('info')->willReturnCallback(function (string $message) {
            $this->assertEquals('RotationMaster: была запущена стратегия strategy', $message);
        });
        $this->logger->expects($this->once())->method('info');

        $this->rotation_master->execute(strtotime('2023-02-02 00:00:00'));
    }
}
