<?php

namespace PK\Tracks;

use Medoo\Medoo;
use PK\Tracks\Track\Track;

class TrackRepository
{
    public function __construct(
        private Medoo $db
    ) {
    }

    /**
     * Выполняет поиск композиций, возвращает список найденных композиций и их общее количество для заданных фильтров
     *
     * @param array  $filters          Список фильтров
     * @param int    $filters[$offset] Смещение относительно первой композиции в списке
     * @param int    $filters[$limit]  Количество композиций в ответе
     * @param string $filters[$artist] Подстрока для поиска по исполнителю
     * @param string $filters[$title]  Подстрока для поиска по имени композиции
     * @param array  $sorting          Список полей и направления для сортировки в виде ['field' => 'ASC']
     *
     * @return []
     */
    public function findMany(array $filters, array $sorting = ['estimate' => 'DESC']): array
    {
        $conditions = $tracks = [];

        $limiting['LIMIT'] = [
            isset($filters['offset']) ? $filters['offset'] : 0,
            isset($filters['limit']) ? $filters['limit'] : 20
        ];

        if (isset($filters['artist'])) {
            $conditions['AND']['artist[~]'] = "%{$filters['artist']}%";
        }

        if (isset($filters['title'])) {
            $conditions['AND']['title[~]'] = "%{$filters['title']}%";
        }

        $count = $this->db->count('tracks', '*', $conditions);

        if ($count == 0) {
            return [$tracks, 0];
        }

        $track_datas = $this->db->select('tracks', '*', array_merge($conditions, $limiting, ['ORDER' => $sorting]));

        foreach ($track_datas as $track_data) {
            $tracks[] = Track::fromArray($track_data);
        }

        return [$tracks, $count];
    }


}
