<?php

namespace Ridouchire\RadioMetrics;

use Ridouchire\RadioMetrics\DTOs\CollectorData;

interface ICollector
{
    public function getData(): CollectorData;
}
