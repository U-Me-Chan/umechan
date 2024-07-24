<?php

namespace PK\Events\EventCallbacks;

use PK\Boards\Board\Board;
use PK\Boards\IBoardRepository;
use PK\Events\AbstractEventCallback;
use PK\Events\Event\Event;
use PK\Events\Event\EventType;
use PK\Events\IEventRepository;
use PK\Posts\IPostRepository;

class BoardUpdatedHandler extends AbstractEventCallback
{
    public function __construct(
        protected IEventRepository $event_repo,
        protected IBoardRepository $board_repo,
        protected IPostRepository $post_repo
    ) {
    }

    public function __invoke(int $board_id): void
    {
        list(, $threads_count)   = $this->post_repo->findMany(['board_ids' => [$board_id], 'parent_id' => null]);
        list(, $new_posts_count) = $this->post_repo->findMany(['board_id' => $board_id, 'timestamp_from' => time() - (60 * 60 * 24)]);

        try {
            /** @var Board */
            $board = $this->board_repo->findOne(['id' => $board_id]);
        } catch (\OutOfBoundsException) {
            throw new \RuntimeException();
        }

        $board->threads_count   = $threads_count;
        $board->new_posts_count = $new_posts_count;

        $this->board_repo->save($board);

        $event = Event::draft(
            type: EventType::BoardUpdateTriggered,
            board_id: $board_id
        );

        $this->event_repo->save($event);
    }
}
