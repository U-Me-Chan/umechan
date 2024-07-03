<?php

namespace PK\Database;

use PK\Database\Event\Event;
use PK\Database\ARepository;
use PK\Exceptions\Event\EventNotFound;

class EventRepository extends ARepository
{
    private const TABLE = "events";

    private const ID = "id";
    private const EVENT_TYPE = "event_type";
    private const TIMESTAMP = "timestamp";
    private const POST_ID = "post_id";
    private const BOARD_ID = "board_id";

    public function fetch(): array
    {
        $events_data = $this->db->select(self::TABLE, $this->getFields(), [
            "ORDER" => [self::TIMESTAMP => "DESC"],
        ]);

        if (!$events_data) {
            throw new EventNotFound("Не найдено ни одного события");
        }

        $events = [];

        foreach ($events_data as $events_data) {
            $events[] = Event::fromState($events_data);
        }

        return $events;
    }

    public function findById(int $id): Event
    {
        $event_data = $this->db->get(self::TABLE, $this->getFields(), ["id" => $id]);

        if (!$event_data) {
            throw new EventNotFound("События с таким идентификатором не найдено");
        }

        return Event::fromState($event_data);
    }

    public function findFrom(int $timestamp): array
    {
        $events_data = $this->db->select(self::TABLE, $this->getFields(), [
            "timestamp[>=]" => $timestamp,
            "ORDER" => [self::TIMESTAMP => "DESC"],
        ]);

        if (!$events_data) {
            throw new EventNotFound("Не найдено ни одного события");
        }

        $events = [];

        foreach ($events_data as $events_data) {
            $events[] = Event::fromState($events_data);
        }

        return $events;
    }

    public function save(Event $event): int
    {
        $this->db->insert(self::TABLE, [
            "event_type" => $event->getEventType(),
            "timestamp" => $event->getTimestamp(),
            "post_id" => $event->getPostId(),
            "board_id" => $event->getBoardId(),
        ]);

        return $this->db->id();
    }

    private function getFields(): array
    {
        return [self::ID, self::EVENT_TYPE, self::TIMESTAMP, self::POST_ID, self::BOARD_ID];
    }
}
