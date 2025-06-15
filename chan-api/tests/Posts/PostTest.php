<?php

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use PK\Boards\Board\Board;
use PK\Posts\Post;

class PostTest extends TestCase
{
    #[Test]
    public function createThread(): void
    {
        $thread = Post::draft(
            Board::fromArray([
                'id' => 1,
                'tag' => 'rnd',
                'name' => 'Random',
                'threads_count' => 0,
                'new_posts_count' => 0
            ]),
            null,
            'test'
        );

        $this->assertEquals(0, $thread->id);
        $this->assertEquals('Anonymous', $thread->poster);
        $this->assertEquals('', $thread->subject);
        $this->assertEquals('test', $thread->message);
        $this->assertNull($thread->parent_id);
        $this->assertFalse($thread->is_verify);
        $this->assertFalse($thread->is_sticky);
    }

    #[Test]
    public function createPost(): void
    {
        $post = Post::draft(
            Board::fromArray([
                'id' => 1,
                'tag' => 'rnd',
                'name' => 'Random',
                'threads_count' => 0,
                'new_posts_count' => 0
            ]),
            123,
            'foo bar'
        );

        $this->assertEquals(0, $post->id);
        $this->assertEquals('Anonymous', $post->poster);
        $this->assertEquals('', $post->subject);
        $this->assertEquals('foo bar', $post->message);
        $this->assertEquals(123, $post->parent_id);
        $this->assertFalse($post->is_verify);
        $this->assertFalse($post->is_sticky);
    }

    #[Test]
    public function restoreFromArray(): void
    {
        $thread = Post::fromArray([
            'id' => 123,
            'poster' => 'x0ma74',
            'subject' => 'foo',
            'message' => 'bar',
            'timestamp' => strtotime(date('d-m-Y', time())),
            'board_data' => [
                'id' => 1,
                'tag' => 'rnd',
                'name' => 'Random',
                'threads_count' => 1,
                'new_posts_count' => 2
            ],
            'parent_id' => null,
            'updated_at' => strtotime(date('d-m-Y', time())),
            'estimate' => 0,
            'password' => 'test',
            'is_verify' => 'yes',
            'is_sticky' => 'no',
            'replies_count' => 501
        ]);

        $this->assertEquals(123, $thread->id);
        $this->assertEquals('x0ma74', $thread->poster);
        $this->assertEquals('foo', $thread->subject);
        $this->assertEquals('bar', $thread->message);
        $this->assertEquals(date('d-m-Y', strtotime(date('d-m-Y', time()))), date('d-m-Y', $thread->timestamp));
        $this->assertEquals(1, $thread->board->id);
        $this->assertEquals('rnd', $thread->board->tag);
        $this->assertEquals('Random', $thread->board->name);
        $this->assertEquals(2, $thread->board->new_posts_count);
        $this->assertEquals(1, $thread->board->threads_count);
        $this->assertNull($thread->parent_id);
        $this->assertEquals(date('d-m-Y', strtotime(date('d-m-Y', time()))), date('d-m-Y', $thread->updated_at));
        $this->assertEquals('test', $thread->password->toString());
        $this->assertTrue($thread->is_verify);
        $this->assertFalse($thread->is_sticky);
        $this->assertTrue($thread->bump_limit_reached);
    }

    #[Test]
    public function attemptToArray(): void
    {
        $post = Post::fromArray([
            'id' => 123,
            'poster' => 'x0ma74',
            'subject' => 'foo',
            'message' => 'bar',
            'timestamp' => strtotime(date('d-m-Y', time())),
            'board_data' => [
                'id' => 1,
                'tag' => 'rnd',
                'name' => 'Random',
                'threads_count' => 1,
                'new_posts_count' => 2
            ],
            'parent_id' => 1,
            'updated_at' => strtotime(date('d-m-Y', time())),
            'estimate' => 0,
            'password' => 'test',
            'is_verify' => 'yes',
            'is_sticky' => 'no'
        ]);

        $state = $post->toArray();

        foreach ($state as $column => $value) {
            switch ($column) {
                case 'id':
                    $this->assertEquals(123, $value);
                    break;
                case 'poster':
                    $this->assertEquals('x0ma74', $value);
                    break;
                case 'subject':
                    $this->assertEquals('foo', $value);
                    break;
                case 'message':
                    $this->assertEquals('bar', $value);
                    break;
                case 'timestamp':
                case 'updated_at':
                case 'estimate':
                    break;
                case 'parent_id':
                    $this->assertEquals(1, $value);
                    break;
                case 'password':
                    $this->assertEquals('test', $value);
                    break;
                case 'is_verify':
                    $this->assertEquals('yes', $value);
                    break;
                case 'board_id':
                    $this->assertEquals(1, $value);
                    break;
                default:
                    throw new Exception("Неизвестное имя колонки: {$column}");
            }
        }
    }

    #[Test]
    public function attemptJsonSerialize(): void
    {
        $post = Post::fromArray([
            'id' => 123,
            'poster' => 'x0ma74',
            'subject' => 'foo',
            'message' => 'bar',
            'timestamp' => strtotime(date('d-m-Y', time())),
            'board_data' => [
                'id' => 1,
                'tag' => 'rnd',
                'name' => 'Random',
                'threads_count' => 1,
                'new_posts_count' => 2
            ],
            'parent_id' => 1,
            'updated_at' => strtotime(date('d-m-Y', time())),
            'estimate' => 0,
            'password' => 'test',
            'is_verify' => 'yes',
            'is_sticky' => 'no'
        ]);

        $state = $post->jsonSerialize();

        foreach ($state as $prop => $value) {
            switch ($prop) {
                case 'id':
                    $this->assertEquals(123, $value);
                    break;
                case 'poster':
                    $this->assertEquals('x0ma74', $value);
                    break;
                case 'subject':
                    $this->assertEquals('foo', $value);
                    break;
                case 'message':
                    $this->assertEquals('bar', $value);
                    break;
                case 'timestamp':
                case 'updated_at':
                case 'estimate':
                case 'media':
                case 'truncated_message':
                case 'datetime':
                    break;
                case 'parent_id':
                    $this->assertEquals(1, $value);
                    break;
                case 'password':
                    $this->assertEquals('test', $value);
                    break;
                case 'is_verify':
                    $this->assertEquals('yes', $value);
                    break;
                case 'board_id':
                    $this->assertEquals(1, $value);
                    break;
                case 'is_thread':
                    $this->assertFalse($value);
                    break;
                case 'board':
                    $this->assertInstanceOf(Board::class, $value);
                    break;
                case 'replies':
                    $this->assertIsArray($value);
                    break;
                case 'replies_count':
                    $this->assertEquals(0, $value);
                    break;
                case 'is_sticky':
                    $this->assertFalse($value);
                    break;
                case 'bump_limit_reached':
                    $this->assertFalse($value);
                    break;
                default:
                    throw new Exception("Неизвестное имя свойства: {$prop}");
            }
        }
    }
}
