<?php

use PHPUnit\Framework\TestCase;
use Ridouchire\RadioScheduler\GenreSchemas\Night;

class NightTest extends TestCase
{
    public function testGetRandom(): void
    {
        $this->assertContains(Night::getRandom(),Night::getAll());
    }

    public function testGetRandomPatterns(): void
    {
        $this->assertContains(Night::getRandomPattern(),Night::getPatterns());
    }
}
