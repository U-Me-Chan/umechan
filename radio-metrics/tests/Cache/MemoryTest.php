<?php

use PHPUnit\Framework\TestCase;
use Ridouchire\RadioMetrics\Cache\Memory;

class MemoryTest extends TestCase
{
    private Memory $cache;

    public function setUp(): void
    {
        $this->cache = new Memory(
            [
                'listeners'      => 2,
                'listen_seconds' => 123,
                'current_state'  => 'playing'
            ]
        );
    }

    public function testGet(): void
    {
        $this->assertEquals(2, $this->cache->get('listeners'));
        $this->assertEquals(123, $this->cache->get('listen_seconds'));
        $this->assertEquals('playing', $this->cache->get('current_state'));
    }

    public function testSet(): void
    {
        $this->cache->set('foo', 'bar');
        $this->cache->set('listen_seconds', 12);

        $this->assertEquals('bar', $this->cache->get('foo'));
        $this->assertEquals(12, $this->cache->get('listen_seconds'));
    }

    public function testIncrement(): void
    {
        $this->cache->increment('listen_seconds', 1);
        $this->assertEquals(124, $this->cache->get('listen_seconds'));

        $this->cache->increment('counter', 1);
        $this->assertEquals(1, $this->cache->get('counter'));

        $this->expectException(InvalidArgumentException::class);
        $this->cache->increment('current_state', 1);
    }

    public function testDecrement(): void
    {
        $this->cache->decrement('listen_seconds', 3);
        $this->assertEquals(120, $this->cache->get('listen_seconds'));

        $this->cache->decrement('counter', 3);
        $this->assertEquals(3, $this->cache->get('counter'));

        $this->expectException(InvalidArgumentException::class);
        $this->cache->decrement('current_state', 3);
    }
}
