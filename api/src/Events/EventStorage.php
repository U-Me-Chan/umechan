<?php

namespace PK\Events;

use Medoo\Medoo;
use PK\Events\Event;

class EventStorage
{
    public function __construct(private Medoo $db) {}

    public function find(int $limit = 20, int $offset = 0, int $from_timestamp = 0): array
    {
        $conditions = [
            'timestamp[>=]' => $from_timestamp,
            'ORDER' => ['timestamp' => 'ASC'],
        ];

        $limit = ['LIMIT' => [$offset, $limit]];

        $event_datas = $this->db->select('events', '*', array_merge($conditions, $limit));
        $count = $this->db->count('events', $conditions);

        if ($event_datas == null) {
            return [[], 0];
        }

        $events = [];

        foreach ($event_datas as $event_data) {
            $events[] = Event::fromArray($event_data);
        }

        return [$events, $count];
    }

    public function save(Event $event): int
    {
        $id = $event->id;

        $event_data = $event->toArray();
        unset($event_data['id']);

        if ($id == 0) {
            $this->db->insert('events', $event_data);
            return $this->db->id();
        }

        $this->db->update('events', $event_data, ['id' => $id]);

        return $id;
    }

    public function delete(int $id): bool
    {
        /** @var PDOStatement */
        $pdo = $this->db->delete('events', ['id' => $id]);

        if ($pdo->rowCount() == 1) {
            return true;
        }

        throw new \OutOfBoundsException();
    }
}
