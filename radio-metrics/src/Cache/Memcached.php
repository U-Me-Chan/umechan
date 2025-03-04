<?php

namespace Ridouchire\RadioMetrics\Cache;

use Ridouchire\RadioMetrics\ICache;

class Memcached implements ICache
{
    private \Memcached $memcached;

    public function __construct(
        private string $hostname = 'memcached',
        private int $port = 11211
    ) {
        $this->memcached = new \Memcached();
        $this->memcached->addServer($hostname, $port);
    }

    public function set(string $key, mixed $value): void
    {
        $this->memcached->set($key, $value, 0);
    }

    public function get(string $key): mixed
    {
        return $this->memcached->get($key);
    }

    public function increment(string $key, int $value): void
    {
        if ($this->get($key) == false) {
            $this->set($key, $value);

            return;
        }

        $this->memcached->increment($key, $value);
    }

    public function decrement(string $key, int $value): void
    {
        if ($this->get($key) == false) {
            $this->set($key, $value);

            return;
        }

        $this->memcached->decrement($key, $value);
    }
}
