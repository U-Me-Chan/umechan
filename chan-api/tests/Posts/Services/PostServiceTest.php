<?php

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use PK\Boards\Board;
use PK\Boards\Services\BoardService;
use PK\Events\ChanEventBuilder;
use PK\Events\FilestoreEventBuilder;
use PK\Events\MessageBroker;
use PK\Passports\Passport;
use PK\Passports\Services\PassportService;
use PK\Posts\Post;
use PK\Posts\Post\Id;
use PK\Posts\PostStorage;
use PK\Posts\Services\PostRestorator;
use PK\Posts\Services\PostService;
use PK\Posts\Exceptions\ThreadBlockedException;
use PK\Posts\Exceptions\ThreadNotFoundException;
use PK\Services\HookService;

final class PostServiceTest extends TestCase
{
    private MockObject|PostStorage $post_storage;
    private MockObject|BoardService $board_service;
    private MockObject|PassportService $passport_service;
    private MockObject|MessageBroker $message_broker;
    private MockObject|PostRestorator $post_restorator;
    private MockObject|HookService $hook_service;

    private PostService $post_service;

    public function setUp(): void
    {
        /**
         * @var MockObject|PostStorage
         *
         * @phpstan-ignore varTag.noVariable
         */
        $this->post_storage = $this->createMock(PostStorage::class);

        /**
         * @var MockObject|BoardService
         *
         * @phpstan-ignore varTag.noVariable
         */
        $this->board_service = $this->createMock(BoardService::class);

        /**
         * @var MockObject|PassportService
         *
         * @phpstan-ignore varTag.noVariable
         */
        $this->passport_service = $this->createMock(PassportService::class);

        /**
         * @var MockObject|MessageBroker
         *
         * @phpstan-ignore varTag.noVariable
         */
        $this->message_broker = $this->createMock(MessageBroker::class);

        /**
         * @var MockObject|PostRestorator
         *
         * @phpstan-ignore varTag.noVariable
         */
        $this->post_restorator = $this->createMock(PostRestorator::class);

        /**
         * @var MockObject|HookService
         *
         * @phpstan-ignore varTag.noVariable
         */
        $this->hook_service = $this->createMock(HookService::class);

        $this->post_service = new PostService(
            $this->post_storage,
            $this->board_service,
            $this->passport_service,
            $this->message_broker,
            new ChanEventBuilder('foo'),
            new FilestoreEventBuilder('foo'),
            $this->post_restorator,
            $this->hook_service
        );
    }

    #[Test]
    public function restorePostFromEPDSDump(): void
    {
        $this->post_restorator
            ->expects($this->once())
            ->method('extractPostDatasFromEPDSAndSaveToInternalDatabase');

        $this->post_service->restorePostFromEPDSDump(time());
    }

    #[Test]
    public function getThreadWithBoardList(): void
    {
        $this->post_storage
            ->expects($this->once())
            ->method('findById')
            ->willReturn(
                Post::draft(
                    Board::draft('rnd', 'Random'),
                    null, 'test'
                )
            );

        $this->board_service
            ->expects($this->once())
            ->method('getBoardList')
            ->willReturn([
                Board::draft('rnd', 'Random')
            ]);

        $this->post_service->getThread(1);
    }

    #[Test]
    public function getThreadWithoutBoardList(): void
    {
        $this->post_storage
            ->expects($this->once())
            ->method('findById')
            ->willReturn(
                Post::draft(
                    Board::draft('rnd', 'Random'),
                    null,
                    'test'
                )
            );

        $this->board_service
            ->expects($this->exactly(0))
            ->method('getBoardList');

        $this->post_service->getThread(1, no_board_list: true);
    }

    #[Test]
    public function getThreadList(): void
    {
        $this->board_service
            ->expects($this->once())
            ->method('getBoardList')
            ->willReturn([
                Board::draft('rnd', 'Random')
            ]);

        $this->post_storage
            ->expects($this->once())
            ->method('find')
            ->willReturnCallback(function (int $limit, int $offset, array $tags) {
                $this->assertEquals(10, $limit);
                $this->assertEquals(0, $offset);
                $this->assertEquals(['rnd'], $tags);

                return [
                    [Post::draft(
                        Board::draft('rnd', 'Random'),
                        null,
                        'test'
                    )],
                    1
                ];
            });

        list($threads, $count, $boards) = $this->post_service->getThreadList(['rnd'], 10, 0);
    }

    #[Test]
    public function createReplyOnThreadWhichNotFound(): void
    {
        $this->post_storage
            ->method('findById')
            ->willThrowException(new ThreadNotFoundException());

        $this->expectException(ThreadNotFoundException::class);

        $this->post_service->createReplyOnThread(1, 'test');
    }

    #[Test]
    public function createReplyOnBlockedThread(): void
    {
        $this->post_storage
            ->expects($this->once())
            ->method('findById')
            ->willReturnCallback(function () {
                $thread = Post::draft(
                    Board::draft('rnd', 'Random'),
                    null,
                    'test'
                );
                $thread->is_blocked = true;

                return $thread;
            });

        $this->expectException(ThreadBlockedException::class);

        $this->post_service->createReplyOnThread(1, 'test');
    }

    #[Test]
    public function createReplyOnThreadWithPosterKey(): void
    {
        $this->post_storage
            ->expects($this->once())
            ->method('findById')
            ->willReturnCallback(fn() => Post::draft(
                Board::draft('rnd', 'Random'),
                null,
                'test'
            ));

        $this->passport_service
            ->expects($this->once())
            ->method('findByHash')
            ->willReturnCallback(fn() => Passport::draft('test', 'test'));

        $this->post_storage
            ->expects($this->atLeast(2))
            ->method('save')
            ->willReturn(Id::generate());

        $this->message_broker
            ->expects($this->atLeast(2))
            ->method('publish');

        $this->hook_service
            ->expects($this->once())
            ->method('registerHookHandler');

        $this->post_service->createReplyOnThread(1, 'test', ['poster' => 'key']);
    }

    #[Test]
    public function createReplyOnThreadWithBumplimit(): void
    {
        $this->post_storage
            ->expects($this->once())
            ->method('findById')
            ->willReturnCallback(function () {
                $thread = Post::draft(Board::draft('rnd', 'Random'), null, 'test');
                $thread->replies_count = 501;

                return $thread;
            });

        $this->passport_service
            ->expects($this->once())
            ->method('findByHash')
            ->willThrowException(new OutOfBoundsException());

        $this->post_storage
            ->expects($this->atLeast(1))
            ->method('save')
            ->willReturn(Id::generate());

        $this->message_broker
            ->expects($this->atLeast(1))
            ->method('publish');

        $this->hook_service
            ->expects($this->once())
            ->method('registerHookHandler');

        $this->post_service->createReplyOnThread(1, 'test', ['poster' => 'key']);
    }
}
