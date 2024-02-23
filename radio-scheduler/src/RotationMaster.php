<?php

namespace Ridouchire\RadioScheduler;

class RotationMaster
{
    public function __construct(
        private IRotation $rotation
    ) {
    }

    public function execute(): void
    {
        $this->rotation->execute();
    }
}
