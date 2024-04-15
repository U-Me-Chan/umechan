<?php

namespace Ridouchire\RadioScheduler;

use Monolog\Logger;
use Ridouchire\RadioScheduler\RotationType;

class RotationMaster
{
    private array $strategies = [];

    private string $current_strategy = '';

    public function __construct(
        private Logger $log
    ) {
    }

    /**
     * Запускает стратегию ротации очереди радио-потока
     *
     * @param string $strategy
     *
     * @return void
     */
    public function execute(string $strategy_name): void
    {
        if (!isset($this->strategies[$strategy_name])) {
            throw new \RuntimeException("RotationMaster: неизвестная стратегия: {$strategy_name}");
        }

        /** @var IRotation */
        $strategy = $this->strategies[$strategy_name];

        $this->current_strategy = $strategy::NAME;

        $this->log->info('Текущая стратегия: ' . $strategy_name);

        $strategy->execute();
    }

    public function addStrategy(IRotation $rotation): void
    {
        $this->strategies[$rotation::NAME] = $rotation;
    }

    public function getCurrentStrategy(): string
    {
        return $this->current_strategy;
    }

    public function getRandomStrategy(): string
    {
        $strategies = array_keys($this->strategies);
        $key =  array_rand($strategies);

        if ($strategies[$key] == $this->current_strategy) {
            return $this->getRandomStrategy();
        }

        return $strategies[$key];
    }
}
