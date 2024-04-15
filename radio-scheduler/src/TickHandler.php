<?php

namespace Ridouchire\RadioScheduler;

use Ridouchire\RadioScheduler\Utils\TickCounter;

class TickHandler
{
    public function __construct(
        private RotationMaster $rotation_master
    ) {
    }

    public function __invoke(): void
    {
        if (TickCounter::getCount() == 0) {
            $strategy_name = $this->rotation_master->getRandomStrategy();

            $this->rotation_master->execute($strategy_name);

            return;
        }

        $strategy_name = $this->rotation_master->getCurrentStrategy();

        if (TickCounter::getCount() >= (60 * 30)) {
            $strategy_name = $this->rotation_master->getRandomStrategy();

            TickCounter::create(1);
        }

        $this->rotation_master->execute($strategy_name);
    }
}
