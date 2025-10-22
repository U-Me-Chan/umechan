<?php

use PHPUnit\Framework\TestCase;
use PK\Posts\Post\Id;

class IdTest extends TestCase
{
    public function testGenerate(): void
    {
        $id = Id::generate();

        $this->assertInstanceOf(Id::class, $id);
        $this->assertEquals(16, floor(log10($id->value) + 1));
    }
}
