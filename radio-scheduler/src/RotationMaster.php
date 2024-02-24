<?php

namespace Ridouchire\RadioScheduler;


class RotationMaster
{
    private array $strategies = [];

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

        $strategy->execute();
    }

    public function addStrategy(IRotation $Rotation): void
    {
        $this->strategies[$Rotation::NAME] = $Rotation;
    }
}
