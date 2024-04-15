<?php

use PHPUnit\Framework\TestCase;
use Ridouchire\RadioScheduler\GenreSchemas\Day;

class DayTest extends TestCase
{
    public function testGetRandom(): void
    {
        $this->assertContains(Day::getRandom(), Day::getAll());
    }

    public function testGetRandomPatterns(): void
    {
        $this->assertContains(Day::getRandomPattern(), Day::getPatterns());
    }
}
