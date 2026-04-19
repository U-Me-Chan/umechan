<?php

namespace Ridouchire\RadioScheduler\Tasks\Task;

use Ridouchire\RadioScheduler\Tasks\Task\ITrackSource;
use Ridouchire\RadioScheduler\Tasks\Task\TracklistGeneratorType;

final class SequencePosition
{
    /**
     * @param ITrackSource[] $sources
     */
    public function __construct(
        public readonly TracklistGeneratorType $gentype,
        public readonly array $sources,
        public readonly int $min_count,
        public readonly int $max_count
    ) {
    }
}
