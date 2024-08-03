<?php

use PHPUnit\Framework\Attributes\RunClassInSeparateProcess;
use PHPUnit\Framework\TestCase;
use Ridouchire\RadioScheduler\Utils\TickCounter;

#[RunClassInSeparateProcess]
class TickCounterTest extends TestCase
{
    public function testGetCount(): void
    {
        $this->assertEquals(0, TickCounter::getCount());
    }

    public function testCreate(): void
    {
        TickCounter::create(4);
        $this->assertEquals(4, TickCounter::getCount());
    }

    public function testReset(): void
    {
        TickCounter::create(2);
        TickCounter::reset();
        $this->assertEquals(0, TickCounter::getCount());
    }

    public function testTick(): void
    {
        TickCounter::tick();

        $this->assertEquals(1, TickCounter::getCount());
    }
}
