<?php

namespace Ridouchire\RadioScheduler\Tasks\Services;

interface ITracklistGenerator
{
    /**
     * @return string[]|array
     */
    public function build(): array;
}
