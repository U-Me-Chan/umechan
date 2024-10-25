<?php

namespace PK\Events\Repositories;

use InvalidArgumentException;
use Medoo\Medoo;
use OutOfBoundsException;
use PK\Events\Event\Event;
use PK\Events\IEventRepository;

final class MedooEventRepository implements IEventRepository
{
    public function __construct(
        private Medoo $db
    ) {
    }

    /**
     * Выполняет поиск событий согласно фильтрам, возвращает список событий и количество
     *
     * @param array $filters                  Список фильтров
     * @param int   $filters[$timestamp_from] unixtime-метка, от которой следует искать
     * @param array $filters[$types]          Список типов, см. PK\Events\Event\EventType
     *
     * @return array
     */
    public function findMany(
        array $filters = [],
        array $ordering = [
            'timestamp' => 'DESC'
        ]
    ): array
    {
        $conditions['ORDER'] = $ordering;

        $limiting['LIMIT'] = [
            isset($filters['offset']) ? $filters['offset'] : 0,
            isset($filters['limit']) ? $filters['limit'] : 20
        ];

        if (isset($filters['timestamp_from'])) {
            $conditions['timestamp[>=]'] = $filters['timestamp_from'];
        }

        if (isset($filters['types'])) {
            $conditions['event_type'] = $filters['types'];
        }

        $count = $this->db->count('events', $conditions);

        if ($count == 0) {
            return [[], 0];
        }

        $event_datas = $this->db->select('events', '*', array_merge($conditions, $limiting));

        $events = array_map(fn(array $event_data) => Event::fromArray($event_data), $event_datas);

        return [$events, 0];
    }

    /**
     * Выполняет поиск события
     *
     * @param array $filters      Список фильтров
     * @param int   $filters[$id] Идентификатор
     *
     * @throws OutOfBoundsException     Если событие не найдено
     * @throws InvalidArgumentException Если список фильтров пуст
     *
     * @return Event
     */
    public function findOne(array $filters = []): Event
    {
        $conditions = [];

        if ($filters['id']) {
            $conditions['id'] = $filters['id'];
        }

        if (empty($conditions)) {
            throw new \InvalidArgumentException();
        }

        $event_data = $this->db->get('events', '*', $conditions);

        if (!$event_data) {
            throw new \OutOfBoundsException();
        }

        return Event::fromArray($event_data);
    }

    public function save(Event $event)
    {
        $id    = $event->id;
        $state = $event->toArray();
        unset($state['id']);

        if ($id == 0) {
            $this->db->insert('events', $state);

            return $this->db->id();
        }

        $this->db->update('events', $state, ['id' => $id]);

        return $id;
    }
}
