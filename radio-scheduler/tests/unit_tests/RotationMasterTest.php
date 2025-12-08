<?php

use Monolog\Logger;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Ridouchire\RadioScheduler\IRotation;
use Ridouchire\RadioScheduler\RotationMaster;
use Ridouchire\RadioScheduler\RotationStrategies\AverageOrAboveEstimateAndLongStandingStrategy;
use Ridouchire\RadioScheduler\RotationStrategies\BestEstimateStrategy;
use Ridouchire\RadioScheduler\RotationStrategies\NewOrLongStandingStrategy;
use Ridouchire\RadioScheduler\RotationStrategies\RandomStrategy;
use Ridouchire\RadioScheduler\RotationType;

class RotationMasterTest extends TestCase
{
    private Logger|MockObject $logger;
    private RotationMaster $rotation_master;

    public function setUp(): void
    {
        /**
         * @var Logger|MockObject
         *
         * @phpstan-ignore varTag.noVariable
        */
        $this->logger = $this->createMock(Logger::class);

        /** @var AverageOrAboveEstimateAndLongStandingStrategy|MockObject */
        $avg_strategy = $this->createMock(AverageOrAboveEstimateAndLongStandingStrategy::class);

        /** @var BestEstimateStrategy|MockObject */
        $best_strategy = $this->createMock(BestEstimateStrategy::class);

        /** @var NewOrLongStandingStrategy|MockObject */
        $new_strategy = $this->createMock(NewOrLongStandingStrategy::class);

        /** @var RandomStrategy|MockObject */
        $random_strategy = $this->createMock(RandomStrategy::class);

        $this->rotation_master = new RotationMaster(
            $this->logger,
            $avg_strategy,
            $best_strategy,
            $new_strategy,
            $random_strategy
        );
    }

    #[Test]
    public function attemptExecuteWithoutRotations(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Нет стратегии ротации на данный час');

        $this->rotation_master->execute();
    }

    #[Test]
    public function attempAddStrategyByPeriodWithInvalidArguments(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->rotation_master->addStrategyByPeriod(0, 0, RotationType::Random, ['Test']);
        $this->rotation_master->addStrategyByPeriod(10, 5, RotationType::Random, ['Test']);
        $this->rotation_master->addStrategyByPeriod(0, 100, RotationType::Random, ['Test']);
    }

    #[Test]
    public function attempAddStrategyByHourWithInvalidArgument(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->rotation_master->addStrategyByHour(25, RotationType::Random, ['Test']);
        $this->rotation_master->addStrategyByHour(-1, RotationType::Random, ['Test']);
    }

    #[Test]
    public function attemptExecuteBySpecificHour(): void
    {
        $this->rotation_master->addStrategyByHour(0, RotationType::Random, ['Test']);

        $this->logger->method('info')->willReturnCallback(function (string $message) {
            $this->assertEquals('RotationMaster: была запущена стратегия strategy', $message);
        });
        $this->logger->expects($this->once())->method('info');

        $this->rotation_master->execute(strtotime('2023-02-02 00:00:00'));
    }

    #[Test]
    public function attemptExecuteBySpecificWeekdayAndHour(): void
    {
        $this->rotation_master->addStrategyByWeekdayAndHour(0, 3, RotationType::Random, ['Test']);

        $this->logger->method('info')->willReturnCallback(function (string $message) {
            $this->assertEquals('RotationMaster: была запущена стратегия strategy', $message);
        });
        $this->logger->expects($this->once())->method('info');

        $this->rotation_master->execute(strtotime('2025-12-03 00:00:00'));
    }

    #[Test]
    public function attemptExecuteBySpecificDayAndHour(): void
    {
        $this->rotation_master->addStrategyByDayAndHour(3, 0, RotationType::Random, ['Test']);

        $this->logger->method('info')->willReturnCallback(function (string $message) {
            $this->assertEquals('RotationMaster: была запущена стратегия strategy', $message);
        });
        $this->logger->expects($this->once())->method('info');

        $this->rotation_master->execute(strtotime('2025-12-03 00:00:00'));
    }
}
