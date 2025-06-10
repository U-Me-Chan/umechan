<?php

namespace Ridouchire\RadioScheduler;

use Monolog\Logger;

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
     * @param string $strategy_name
     *
     * @return void
     */
    public function execute(string $strategy_name): void
    {
        if (empty($this->strategies)) {
            throw new \Exception("Нет стратегий");
        }

        if (!isset($this->strategies[$strategy_name])) {
            throw new \Exception("RotationMaster: неизвестная стратегия: {$strategy_name}");
        }

        /** @var IRotation */
        $strategy = $this->strategies[$strategy_name];

        if ($strategy::NAME !== $this->current_strategy) {
            $this->log->info('Текущая стратегия: ' . $strategy_name);
        }

        $this->current_strategy = $strategy::NAME;

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
        if (sizeof($this->strategies)) {
            return array_key_last($this->strategies);
        }

        $strategies = array_keys($this->strategies);
        $key =  array_rand($strategies);

        if ($strategies[$key] == $this->current_strategy) {
            return $this->getRandomStrategy();
        }

        return $strategies[$key];
    }
}
