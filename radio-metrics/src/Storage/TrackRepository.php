<?php

namespace Ridouchire\RadioMetrics\Storage;

use InvalidArgumentException;
use Medoo\Medoo;
use PDOStatement;
use Ridouchire\RadioMetrics\Exceptions\EntityNotFound;
use Ridouchire\RadioMetrics\Storage\Entites\Track;
use Ridouchire\RadioMetrics\Storage\IRepository;
use RuntimeException;

class TrackRepository implements IRepository
{
    public function __construct(
        private Medoo $db
    ) {
    }

    /**
     * Выполняет поиск композиций по заданным фильтрам
     *
     * @param array  $filters          Список фильтров
     * @param int    $filters[$offset] Смещение относительно первого элемента в списке
     * @param int    $filters[$limit]  Количество композиций в ответе
     * @param string $filters[$artist] Подстрока для поиска по исполнителю
     * @param string $filters[$title]  Подстрока для поиска по названию
     *
     * @return array
     */
    public function findMany(array $filters = []): array
    {
        $conditions = $limiting = [];

        $limiting['LIMIT'] = [
            isset($filters['offset']) ? $filters['offset'] : 0,
            isset($filters['limit']) ? $filters['limit'] : 10
        ];

        if (isset($filters['artist'])) {
            $conditions['AND']['artist[~]'] = "%{$filters['artist']}%";
        }

        if (isset($filters['title'])) {
            $conditions['AND']['title[~]'] = "%{$filters['title']}%";
        }

        /** @var int */
        $count = $this->db->count('tracks', $conditions);

        if ($count == 0) {
            return [[], 0];
        }

        /** @var array */
        $track_datas = $this->db->select('tracks', '*', array_merge($conditions, $limiting));

        $tracks = [];

        foreach ($track_datas as $track_data) {
            $tracks[] = Track::fromArray($track_data);
        }

        return [$tracks, $count];
    }

    /**
     * Выполняет поиск композиции
     *
     * @param array $filters                Список фильтров
     * @param int   $filters[$id]           Идентификатор трека
     * @param int   $filters[$mpd_track_id] Идентификатор трека в MPD
     *
     * @throws InvalidArgumentException Если список фильтров пустов
     * @throws EntityNotFound           Если композиции не найдено
     *
     * @return Track
     */
    public function findOne(array $filters = []): Track
    {
        if (empty($filters)) {
            throw new \InvalidArgumentException("Список фильтров не может быть пустым");
        }

        $conditions = [];

        if (isset($filters['id'])) {
            $conditions['id'] = $filters['id'];
        }

        if (isset($filters['mpd_track_id'])) {
            $conditions['mpd_track_id'] = $filters['mpd_track_id'];
        }

        if (empty($conditions)) {
            throw new \InvalidArgumentException("Список условий пуст");
        }

        /** @var array|false */
        $track_data = $this->db->get('tracks', '*', $conditions);

        if (!$track_data) {
            throw new EntityNotFound("Композиция не найдена");
        }

        return Track::fromArray($track_data);
    }

    /**
     * Сохраняет данные композиции, возвращает её идентификатор
     *
     * @param Track $track Композиция
     *
     * @throws InvalidArgumentException
     *
     * @return int
     */
    public function save($track): int
    {
        if (!$track instanceof Track) {
            throw new \InvalidArgumentException();
        }

        $track_data = $track->toArray();
        $id         = $track_data['id'];
        unset($track_data['id']);

        if ($id == 0) {
            $this->db->insert('tracks', $track_data);

            return $this->db->id();
        }

        $this->db->update('tracks', $track_data, ['id' => $id]);

        return $id;
    }

    /**
     * Удаляет данные композиции
     *
     * @param Track $track Композиция
     *
     * @throws InvalidArgumentException
     * @throws RuntimeException
     *
     * @return true
     */
    public function delete($track): bool
    {
        if (!$track instanceof Track) {
            throw new \InvalidArgumentException();
        }

        if ($track->getId() == 0) {
            throw new \InvalidArgumentException();
        }

        /** @var PDOStatement */
        $pdo = $this->db->delete('tracks', ['id' => $track->getId()]);

        if ($pdo->rowCount() == 1) {
            return true;
        }

        throw new \RuntimeException();
    }
}
