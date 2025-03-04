<?php

namespace Ridouchire\RadioMetrics\Cache;

use Ridouchire\RadioMetrics\ICache;

class Memory implements ICache
{
    public function __construct(
        private array $map = []
    ) {
    }

    public function set(string $key, mixed $value): void
    {
        $this->map[$key] = $value;
    }

    public function get(string $key): mixed
    {
        if (isset($this->map[$key])) {
            return $this->map[$key];
        }

        return false;
    }

    public function increment(string $key, int $value): void
    {
        if (isset($this->map[$key]) && is_int($this->map[$key])) {
            $this->map[$key] = $this->map[$key] + $value;

            return;
        }

        if (isset($this->map[$key]) && !is_int($this->map[$key])) {
            throw new \InvalidArgumentException('Значение ключа не является инкрементируемым по типу');
        }

        $this->map[$key] = $value;
    }

    public function decrement(string $key, int $value): void
    {
        if (isset($this->map[$key]) && is_int($this->map[$key])) {
            $this->map[$key] = $this->map[$key] - $value;

            return;
        }

        if (isset($this->map[$key]) && !is_int($this->map[$key])) {
            throw new \InvalidArgumentException('Значение ключа не является инкрементируемым по типу');
        }

        $this->map[$key] = $value;
    }
}
