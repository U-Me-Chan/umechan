<?php

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use PK\Boards\Board;
use PK\Boards\BoardStorage;
use PK\Boards\Services\BoardService;
use PK\Events\ChanEventBuilder;
use PK\Events\Message;
use PK\Events\MessageBroker;

final class BoardServiceTest extends TestCase
{
    public BoardService $board_service;
    public ChanEventBuilder $chan_event_builder;
    public MockObject|MessageBroker $message_broker;
    public MockObject|BoardStorage $board_storage;

    public function setUp(): void
    {
        /** @var MockObject|MessageBroker */
        $this->message_broker = $this->createMock(MessageBroker::class); // @phpstan-ignore varTag.noVariable

        /** @var MockObject|BoardStorage */
        $this->board_storage = $this->createMock(BoardStorage::class); // @phpstan-ignore varTag.noVariable

        /** @var ChanEventBuilder */
        $this->chan_event_builder = new ChanEventBuilder('foo'); // @phpstan-ignore varTag.noVariable

        $this->board_service = new BoardService(
            $this->board_storage,
            $this->message_broker,
            $this->chan_event_builder
        );
    }

    #[Test]
    public function createBoard(): void
    {
        $this->board_storage->method('save')->willReturn(1);
        $this->message_broker->expects($this->once())
            ->method('publish')
            ->willReturnCallback(function (Message $message) {
                $this->assertEquals('chan.boards', $message->topic);
                $this->assertJsonStringEqualsJsonString(
                    json_encode([
                        'eventName' => 'CreateBoard',
                        'nodeSign'  => 'foo',
                        'payload'   => [
                            'board' => [
                                'id'              => 1,
                                'name'            => 'Random',
                                'tag'             => 'rnd',
                                'new_posts_count' => 0,
                                'threads_count'   => 0
                            ]
                        ]
                    ]),
                    $message->json
                );
            });

        $id = $this->board_service->createBoard('rnd', 'Random');

        $this->assertEquals(1, $id);
    }

    #[Test]
    public function renameBoardByTag(): void
    {
        $this->board_storage->expects($this->once())
            ->method('findByTag')
            ->willReturn(Board::fromArray([
                'id' => 1,
                'tag' => 'rnd',
                'name' => 'Random'
            ]));
        $this->message_broker->expects($this->once())
            ->method('publish')
            ->willReturnCallback(function (Message $message) {
                $this->assertEquals('chan.boards', $message->topic);
                $this->assertJsonStringEqualsJsonString(
                    json_encode([
                        'eventName' => 'UpdateBoard',
                        'nodeSign'  => 'foo',
                        'payload'   => [
                            'board' => [
                                'id'              => 1,
                                'name'            => 'Bred',
                                'tag'             => 'b',
                                'new_posts_count' => 0,
                                'threads_count'   => 0
                            ]
                        ]
                    ]),
                    $message->json
                );
            });

        $this->board_service->renameBoardByTag(tag: 'rnd', new_tag: 'b', new_name: 'Bred');
    }

    #[Test]
    public function updateNewPostsCount(): void
    {
        $this->message_broker->expects($this->once())
            ->method('publish')
            ->willReturnCallback(function (Message $message) {
                $this->assertEquals('chan.boards', $message->topic);
            $this->assertJsonStringEqualsJsonString(
                json_encode([
                    'eventName' => 'UpdateBoard',
                    'nodeSign'  => 'foo',
                    'payload'   => [
                        'board' => [
                            'id'              => 1,
                            'name'            => 'test',
                            'tag'             => 'test',
                            'new_posts_count' => 3,
                            'threads_count'   => 1
                        ]
                    ]
                ]),
                $message->json
            );
            });
        $this->board_service->updateNewPostsCount(Board::fromArray([
            'id' => 1,
            'tag' => 'test',
            'name' => 'test',
            'new_posts_count' => 3,
            'threads_count'   => 1
        ]));
    }

    #[Test]
    public function updateThreadsCount(): void
    {
        $this->message_broker->expects($this->once())
            ->method('publish')
            ->willReturnCallback(function (Message $message) {
                $this->assertEquals('chan.boards', $message->topic);
                $this->assertJsonStringEqualsJsonString(
                    json_encode([
                        'eventName' => 'UpdateBoard',
                        'nodeSign'  => 'foo',
                        'payload'   => [
                            'board' => [
                                'id'              => 1,
                                'name'            => 'test',
                                'tag'             => 'test',
                                'new_posts_count' => 1,
                                'threads_count'   => 1
                            ]
                        ]
                    ]),
                    $message->json
                );
            });
        $this->board_service->updateThreadsCount(Board::fromArray([
            'id' => 1,
            'tag' => 'test',
            'name' => 'test',
            'new_posts_count' => 1,
            'threads_count'   => 1
        ]));
    }

    #[Test]
    public function getBoardList(): void
    {
        $this->board_storage->expects($this->once())->method('find');
        $this->board_service->getBoardList();
    }

    #[Test]
    public function getBoardByTag(): void
    {
        $this->board_storage->expects($this->once())->method('findByTag');
        $this->board_service->getBoardByTag('test');
    }
}
