<?php

namespace Ridouchire\RadioScheduler\Tasks\Task\TrackSources;

use Ridouchire\RadioScheduler\Tasks\Task\ITrackSource;

final class DirectorySource implements ITrackSource
{
    public function __construct(
        public readonly array $dirs,
    ) {
    }
}
