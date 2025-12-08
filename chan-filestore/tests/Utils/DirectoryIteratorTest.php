<?php

use IH\Utils\DirectoryIterator;
use PHPUnit\Framework\TestCase;

class DirectoryIteratorTest extends TestCase
{
    private DirectoryIterator $iterator;

    public function setUp(): void
    {
        $this->iterator = new DirectoryIterator(__DIR__ . DIRECTORY_SEPARATOR . '[{DirectoryIteratorTest}]*', GLOB_BRACE);
    }

    public function testCount(): void
    {
        $this->assertEquals(1, $this->iterator->count());
    }

    public function testCurrent(): void
    {
        $this->assertInstanceOf(SplFileInfo::class, $this->iterator->current());
        $this->assertEquals(__FILE__, $this->iterator->current()->getPathname());
    }

    public function testNext(): void
    {
        $this->iterator->next();

        $this->expectException(RuntimeException::class);

        $this->iterator->current();
    }
}
