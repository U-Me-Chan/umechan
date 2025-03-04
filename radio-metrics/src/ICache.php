<?php

namespace Ridouchire\RadioMetrics;

interface ICache
{
    public function set(string $key, mixed $value): void;
    public function get(string $key): mixed;
    public function increment(string $key, int $value): void;
    public function decrement(string $key, int $value): void;
}
