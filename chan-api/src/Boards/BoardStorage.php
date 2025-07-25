<?php

namespace PK\Boards;

use OutOfBoundsException;
use Medoo\Medoo;
use PK\Boards\Board\Board;

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

    /**
     * @throws OutOfBoundsException Если доска не найдена
     */
    public function findByTag(string $tag): Board
    {
        $board_data = $this->db->get('boards', '*', ['tag' => $tag]);

        if ($board_data == null) {
            throw new \OutOfBoundsException();
        }

        return Board::fromArray($board_data);
    }

    public function findById(int $id): Board
    {
        $board_data = $this->db->get('boards', '*', ['id' => $id]);

        if ($board_data == null) {
            throw new \OutOfBoundsException();
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

    public function updateCounters(int $id): void
    {
        $threads_count   = $this->db->count('posts', ['board_id' => $id, 'parent_id' => null]);
        $new_posts_count = $this->db->count('posts', ['board_id' => $id, 'timestamp[>]' => time() - (60 * 60 * 24)]);

        $board = $this->findById($id);

        $board->new_posts_count = $new_posts_count;
        $board->threads_count   = $threads_count;

        $this->save($board);
    }
}
