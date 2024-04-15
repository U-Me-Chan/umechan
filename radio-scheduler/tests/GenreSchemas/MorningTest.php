<?php

use PHPUnit\Framework\TestCase;
use Ridouchire\RadioScheduler\GenreSchemas\Morning;

class MorningTest extends TestCase
{
    public function testGetRandom(): void
    {
        $this->assertContains(Morning::getRandom(),Morning::getAll());
    }

    public function testGetRandomPatterns(): void
    {
        $this->assertContains(Morning::getRandomPattern(),Morning::getPatterns());
    }
}
