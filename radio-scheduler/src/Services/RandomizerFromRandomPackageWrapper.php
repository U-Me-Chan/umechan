<?php

namespace Ridouchire\RadioScheduler\Services;

use Random\Randomizer;
use Ridouchire\RadioScheduler\IRandomizer;

class RandomizerFromRandomPackageWrapper implements IRandomizer
{
    private Randomizer $randomizer;

    public function __construct(
    ) {
        $this->randomizer = new Randomizer();
    }

    public function getInt(int $min, int $max): int
    {
        return $this->randomizer->getInt($min, $max);
    }
}
