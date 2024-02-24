<?php

namespace Ridouchire\RadioScheduler;

class RotationMaster
{
    public function __construct(
        private IRotation $rotation
    ) {
    }

    /**
     * Запускает стратегию ротации очереди радио-потока
     *
     * @return void
     */
    public function execute(): void
    {
        ##TODO: в будущее время здесь будет выбор определённой стратегии, исходя из внешних условий
        $this->rotation->execute();
    }
}
