<?php

namespace PK\Cache;

use Memcached as MemcachedDriver;
use PK\Cache;

class Memcached implements Cache
{
    private MemcachedDriver $memcached;

    public function __construct(
        private string $host = 'memcached',
        private int $port = 11211
    ) {
        $this->memcached = new MemcachedDriver();
        $this->memcached->addServer($this->host, $this->port);
        $this->memcached->setOption(MemcachedDriver::OPT_COMPRESSION, true);
        $this->memcached->setOption(MemcachedDriver::OPT_TCP_KEEPALIVE, true);
        $this->memcached->setOption(MemcachedDriver::OPT_BINARY_PROTOCOL, true);
        $this->memcached->setOption(MemcachedDriver::OPT_TCP_NODELAY, true);
    }

    public function get(string $key, mixed $default_value = null): mixed
    {
        $value = $this->memcached->get($key);

        return $value === false ? $default_value : $value;
    }

    public function set(string $key, mixed $value, int $expiration_time = 0): void
    {
        $this->memcached->set($key, $value, $expiration_time);
    }

    public function delete(string $key): void
    {
        $this->memcached->delete($key);
    }
}
