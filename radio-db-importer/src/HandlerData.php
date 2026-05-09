<?php

namespace Ridouchire\RadioDbImporter;

use SplFileInfo;
use Ridouchire\RadioDbImporter\Tracks\Track;

final class HandlerData
{
    public function __construct(
        public SplFileInfo $file,
        public ?Track $track = null
    ) {
    }
}
