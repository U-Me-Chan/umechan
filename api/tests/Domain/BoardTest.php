<?php

use PHPUnit\Framework\TestCase;
use PK\Domain\Board;

final class BoardTest extends TestCase
{
    public function testFromArray(): void
    {
        $board = Board::fromArray([
            'id' => 12,
            'tag' => 'rnd',
            'name' => 'Random',
            'threads_count' => 10,
            'new_post_count' => 0
        ]);

        $this->assertInstanceOf(Board::class, $board);

        $this->assertEquals(12, $board->getId());
        $this->assertEquals('rnd', $board->getTag());
        $this->assertEquals('name', $board->getName());
        $this->assertEquals('threads_count', $board->getThreadsCount());
        $this->assertEquals('new_posts_count', $board->getNewPostsCount());
    }
}
