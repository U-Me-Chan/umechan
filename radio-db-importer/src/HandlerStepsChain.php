<?php

namespace Ridouchire\RadioDbImporter;

use Throwable;
use React\Promise\Promise;
use React\Promise\PromiseInterface;

class HandlerStepsChain
{
    private array $handlers = [];

    public function addHandler(HandleStep $handle_step): self
    {
        $this->handlers[] = $handle_step;

        return $this;
    }

    /**
     * @return Promise<HandlerData|Throwable>
     */
    public function process(HandlerData $handler_data): PromiseInterface
    {
        return $this->processHandleStep($handler_data, 0);
    }

    /**
     * @return Promise<HandlerData|Throwable>
     */
    private function processHandleStep(HandlerData $handler_data, int $index): PromiseInterface
    {
        if ($index >= count($this->handlers)) {
            return new Promise(function ($resolve) use ($handler_data) {
                $resolve($handler_data);
            });
        }

        $handler_step = $this->handlers[$index];

        return $handler_step->process($handler_data)->then(
            function ($result) use ($index) {
                return $this->processHandleStep($result, $index + 1);
            }
        );
    }
}
