<?php

namespace Ridouchire\RadioDbImporter;

use Throwable;
use React\Promise\PromiseInterface;

interface HandleStep
{
    /**
     * @return PromiseInterface<HandlerData|Throwable>
     */
    public function process(HandlerData $data): PromiseInterface;
}
