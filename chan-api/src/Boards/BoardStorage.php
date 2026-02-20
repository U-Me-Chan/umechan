<?php

namespace PK\Boards;

use Medoo\Medoo;
use PK\Base\Timestamp;
use PK\Boards\Board;
use PK\Boards\Exceptions\BoardNotFoundException;

class BoardStorage
{
    public function __construct(
        private Medoo $db
    ) {
    }

    public function find(array $exclude_tags = [], array $tags = []): array
    {
        $conditions = [
            'ORDER' => ['tag' => 'ASC']
        ];

        if (!empty($exclude_tags)) {
            $conditions['tag[!]'] = $exclude_tags;
        }

        if (!empty($tags)) {
            $conditions['tag'] = $tags;
        }

        $board_datas = $this->db->select('boards', '*', $conditions);

        if (!$board_datas) {
            return [];
        }

        return array_map(fn(array $board_data) => Board::fromArray($board_data), $board_datas);
    }

    public function findByTag(string $tag): Board
    {
        $board_data = $this->db->get('boards', '*', ['tag' => $tag]);

        if ($board_data == null) {
            throw new BoardNotFoundException();
        }

        return Board::fromArray($board_data);
    }

    public function findById(int $id): Board
    {
        $board_data = $this->db->get('boards', '*', ['id' => $id]);

        if ($board_data == null) {
            throw new BoardNotFoundException();
        }

        return Board::fromArray($board_data);
    }

    public function save(Board $board): int
    {
        $id = $board->id;

        $board_data = $board->toArray();

        unset($board_data['id']);

        if ($id == 0) {
            $this->db->insert('boards', $board_data);

            return $this->db->id();
        }

        $this->db->update('boards', $board_data, ['id' => $id]);

        return $id;
    }

    public function updateThreadsCount(Board $board): void
    {
        $threads_count = $this->db->count('posts', ['board_id' => $board->id, 'parent_id' => null]);

        $board->threads_count = $threads_count;

        $this->save($board);
    }

    public function updateNewPostsCount(Board $board): void
    {
        $date = Timestamp::draft()->toString();

        $start_timestamp = Timestamp::fromString($date)->toInt();
        $end_timestamp   = Timestamp::fromString($date);
        $end_timestamp->increase(days: 1);
        $end_timestamp = $end_timestamp->toInt();

        $new_posts_count = $this->db->count(
            'posts',
            [
                'board_id'     => $board->id,
                'timestamp[>]' => $start_timestamp,
                'timestamp[<]' => $end_timestamp
            ]
        );

        $board->new_posts_count = $new_posts_count;

        $this->save($board);
    }
}
