<?php

use PHPUnit\Framework\TestCase;
use Ridouchire\RadioScheduler\GenreSchemas\Evening;

class EveningTest extends TestCase
{
    public function testGetRandom(): void
    {
        $this->assertContains(Evening::getRandom(),Evening::getAll());
    }

    public function testGetRandomPatterns(): void
    {
        $this->assertContains(Evening::getRandomPattern(),Evening::getPatterns());
    }
}
