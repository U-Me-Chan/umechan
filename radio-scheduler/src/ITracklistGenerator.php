<?php

namespace Ridouchire\RadioScheduler;

interface ITracklistGenerator
{
    /**
     * @return string[]|array
     */
    public function build(): array;
}
