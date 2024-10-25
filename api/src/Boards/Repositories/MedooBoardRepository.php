<?php

namespace PK\Boards\Repositories;

use InvalidArgumentException;
use Medoo\Medoo;
use OutOfBoundsException;
use PK\Boards\IBoardRepository;
use PK\Boards\Board\Board;

final class MedooBoardRepository implements IBoardRepository
{
    public function __construct(
        private Medoo $db
    ) {
    }

    /**
     * Возвращает список досок и их количество
     *
     * @param array $filters                Список фильтов
     * @param array $filters[$exclude_tags] Список исключаемых тегов
     * @param array $filters[$tags]         Список тегов
     *
     * @return array
     */
    public function findMany(
        array $filters = [],
        array $ordering = [
            'tag' => 'ASC'
        ]
    ): array
    {
        $conditions = [];
        $limiting['LIMIT'] = [
            isset($filters['offset']) ? $filters['offset'] : 0,
            isset($filters['limit']) ? $filters['limit']: 50
        ];

        if (isset($filters['exclude_tags'])) {
            $conditions['tag[!]'] = $filters['exclude_tags'];
        }

        if (isset($filters['tags'])) {
            $conditions['tag'] = $filters['tag'];
        }

        $count = $this->db->count('boards', $conditions);

        if ($count == 0) {
            return [[], 0];
        }

        $board_datas = $this->db->select('boards', '*', array_merge($conditions, $limiting, ['ORDER' => $ordering]));
        $boards = array_map(fn(array $board_data) => Board::fromArray($board_data), $board_datas);

        return [$boards, $count];
    }

    /**
     * Выполняет поиск доски согласно фильтрам
     *
     * @param array  $filters
     * @param int    $filters[$id]
     * @param string $filters[$tag]
     *
     * @throws InvalidArgumentException
     * @throws OutOfBoundsException
     *
     * @return Board
     */
    public function findOne(array $filters = []): Board
    {
        $conditions = [];

        if (isset($filters['id'])) {
            $conditions['id'] = $filters['id'];
        }

        if (isset($filters['tag'])) {
            $conditions['tag'] = $filters['tag'];
        }

        if (empty($conditions)) {
            throw new \InvalidArgumentException();
        }

        $board_data = $this->db->get('boards', '*', $conditions);

        if (!$board_data) {
            throw new \OutOfBoundsException();
        }

        return Board::fromArray($board_data);
    }

    public function save(Board $board): int
    {
        $id = $board->id;
        $state = $board->toArray();
        unset($state['id']);

        if ($id == 0) {
            $this->db->insert('boards', $state);

            return $this->db->id();
        }

        $this->db->update('boards', $state, ['id' => $id]);

        return $id;
    }

    public function delete(Board $board): bool
    {
        return true;
    }
}
