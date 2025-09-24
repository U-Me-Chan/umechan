<?php

namespace Ridouchire\RadioScheduler;

use InvalidArgumentException;
use Monolog\Logger;
use RuntimeException;

class RotationMaster
{
    private array $strategies = [];

    public function __construct(
        private Logger $log
    ) {
        $this->strategies = array_fill(0, 23, []);
    }

    public function execute(?int $timestamp = null): void
    {
        $hour = (int) date('G', $timestamp);

        if (!isset($this->strategies[$hour]) || empty($this->strategies[$hour])) {
            throw new RuntimeException('Нет стратегии ротации на данный час');
        }

        /** @var IRotation $strategy  */
        foreach ($this->strategies[$hour] as $strategy) {
            if ($strategy->isFired($hour)) {
                $strategy->execute();

                $this->log->info("RotationMaster: была запущена стратегия " . $strategy::NAME);

                return;
            }
        }

        throw new RuntimeException('Нет стратегии ротации на данный час');
    }

    public function addStrategyByHour(int $hour, IRotation $rotation): void
    {
        if (!$this->isValidHour($hour)) {
            throw new InvalidArgumentException();
        }

        $this->strategies[$hour][] = $rotation;
    }

    public function addStrategyByPeriod(int $from, int $to, IRotation $rotation): void
    {
        if (!$this->isValidHour($from)) {
            throw new InvalidArgumentException();
        }

        if (!$this->isValidHour($to)) {
            throw new InvalidArgumentException();
        }

        if ($to < $from) {
            throw new InvalidArgumentException();
        }

        if ($from == $to) {
            throw new InvalidArgumentException();
        }

        $hours = range($from, $to);

        array_walk($hours, function (int $hour) use ($rotation) {
            $this->strategies[$hour][] = $rotation;
        });
    }

    private function isValidHour(int $hour): bool
    {
        return $hour < 0 || $hour > 23 ? false : true;
    }
}
