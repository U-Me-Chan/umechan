<?php

namespace Ridouchire\RadioScheduler;

interface IRandomizer
{
    public function getInt(int $min, int $max): int;
}
