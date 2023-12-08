<?php

namespace Ridouchire\RadioMetrics\Storage;

use InvalidArgumentException;
use Medoo\Medoo;
use PDOStatement;
use Ridouchire\RadioMetrics\Exceptions\EntityNotFound;
use Ridouchire\RadioMetrics\Storage\IRepository;
use Ridouchire\RadioMetrics\Storage\Entites\Record;
use RuntimeException;

class RecordRepository implements IRepository
{
    public function __construct(
        private Medoo $db
    ) {
    }

    /**
     * Выполняет поиск записей о слушателях на треках согласно фильтрам,
     * возвращает список записей их их количество
     *
     * @param array $filters            Список фильтров
     * @param int   $filters[$track_id] Идентификатор трека
     * @param int   $filters[$offset]   Смещение относительно первой записи в списке
     * @param int   $filters[$limit]    Количество возвращаемых записей
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

        if (isset($filters['track_id'])) {
            $conditions['AND']['track_id'] = $filters['track_id'];
        }

        $count = $this->db->count('records', $conditions);

        if ($count == 0) {
            return [[], 0];
        }

        $record_datas = $this->db->select('records', '*', array_merge($conditions, $limiting));

        $records = [];

        foreach ($record_datas as $record_data) {
            $records[] = Record::fromArray($record_data);
        }

        return [$records, $count];
    }

    /**
     * Выполняет поиск записи о слушателях на треке согласно фильтрам
     *
     * @param array $filters             Список фильтров
     * @param int   $filters[$id]        Идентификатор записи
     * @param int   $filters[$timestamp] unixtime-метка времени
     *
     * @throws EntityNotFound           Если записи не найдено
     * @throws InvalidArgumentException Если список фильтров пустов или некорректен
     *
     * @return Record
     */
    public function findOne(array $filters = []): Record
    {
        if (empty($filters)) {
            throw new \InvalidArgumentException("Список фильтров не может быть пустым");
        }

        $conditions = [];

        if (isset($filters['id'])) {
            $conditions['id'] = $filters['id'];
        }

        if (empty($conditions)) {
            throw new \InvalidArgumentException("В списке фильтров нет допустимых фильтров");
        }

        /** @var array|false */
        $record_data = $this->db->get('records', '*', $conditions);

        if (!$record_data) {
            throw new EntityNotFound("Записи не найдено");
        }

        return Record::fromArray($record_data);
    }

    /**
     * Сохраняет запись о слушателях для трека, возвращает идентификатор записи
     *
     * @param Record $record Запись
     *
     * @throws InvalidArgumentException Если вместо записи передано что-то иное
     *
     * @return int
     */
    public function save($record): int
    {
        if (!$record instanceof Record) {
            throw new \InvalidArgumentException("Это не запись о слушателях трека");
        }

        $record_data = $record->toArray();
        unset($record_data['id']);

        $this->db->insert('records', $record_data);

        return $this->db->id();
    }

    /**
     * Удаляет запись
     *
     * @param Record $record Запись
     *
     * @throws RuntimeException         Если запись не удалось удалить
     * @throws InvalidArgumentException Если вместо записи передано что-то иное
     *
     * @return true
     */
    public function delete($record): bool
    {
        if (!$record instanceof Record) {
            throw new \InvalidArgumentException("Это не запись о слушателях трека");
        }

        if ($record->getId() == 0) {
            throw new \InvalidArgumentException("Нельзя удалить черновик записи");
        }

        /** @var PDOStatement */
        $pdo = $this->db->delete('records', ['id' => $record->getId()]);

        if ($pdo->rowCount() == 1) {
            return true;
        }

        throw new \RuntimeException("Запись не удалена");
    }
}
