<?php

namespace PK\Boards\Services;

use PK\Boards\Board;
use PK\Boards\BoardStorage;
use PK\Boards\Events\ChanEvents\CreateBoard;
use PK\Boards\Events\ChanEvents\CreateBoardPayload;
use PK\Boards\Events\ChanEvents\UpdateBoard;
use PK\Boards\Events\ChanEvents\UpdateBoardPayload;
use PK\Events\ChanEventBuilder;
use PK\Events\MessageBroker;

final class BoardService
{
    public function __construct(
        private BoardStorage $board_storage,
        private MessageBroker $message_broker,
        private ChanEventBuilder $chan_event_builder
    ) {
    }

    public function getBoardList(array $exclude_tags = []): array
    {
        return $this->board_storage->find($exclude_tags);
    }

    public function getBoardByTag(string $tag): Board
    {
        return $this->board_storage->findByTag($tag);
    }

    public function createBoard(string $tag, string $name): int
    {
        $id = $this->board_storage->save(Board::draft($tag, $name));

        $board = Board::fromArray([
            'id'   => $id,
            'tag'  => $tag,
            'name' => $name
        ]);

        $this->message_broker->publish(
            $this->chan_event_builder->build(
                CreateBoard::class,
                new CreateBoardPayload($board)
            )
        );

        return $id;
    }

    public function renameBoardByTag(
        string $tag,
        ?string $new_tag = null,
        ?string $new_name = null
    ): void
    {
        $board = $this->board_storage->findByTag($tag);

        if ($new_tag) {
            $board->tag = $new_tag;
        }

        if ($new_name) {
            $board->name = $new_name;
        }

        $this->board_storage->save($board);

        $this->message_broker->publish(
            $this->chan_event_builder->build(
                UpdateBoard::class,
                new UpdateBoardPayload($board)
            )
        );
    }

    public function updateNewPostsCount(Board $board): void
    {
        $this->board_storage->updateNewPostsCount($board);

        $this->message_broker->publish(
            $this->chan_event_builder->build(
                UpdateBoard::class,
                new UpdateBoardPayload($board)
            )
        );
    }

    public function updateThreadsCount(Board $board): void
    {
        $this->board_storage->updateThreadsCount($board);

        $this->message_broker->publish(
            $this->chan_event_builder->build(
                UpdateBoard::class,
                new UpdateBoardPayload($board)
            )
        );
    }
}
