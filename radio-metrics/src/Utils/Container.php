<?php

namespace Ridouchire\RadioMetrics\Utils;

class Container
{
    public function __construct(
        private array $map = []
    ) {
    }

    public function __set($name, $value)
    {
        $this->map[$name] = $value;
    }

    public function __get($name)
    {
        if (isset($this->map[$name])) {
            return $this->map[$name];
        }

        throw new \Exception();
    }
}
