<?php

namespace Ridouchire\RadioMetrics\Storage;

use Medoo\Medoo;
use PDOStatement;
use Ridouchire\RadioMetrics\Storage\AEntity;

class Repository
{
    public function __construct(
        private Medoo $db
    ) {
    }

    ##TODO
    public function findMany()
    {
    }

    public function findOne(AEntity $entity, array $filters = []) // костыль, но что поделать
    {
        $data = $this->db->get($this->getTable($entity), '*', !empty($filters) ? $filters : null);

        if (!$data) {
            throw new \DomainException();
        }

        return $entity::fromArray($data);
    }

    public function save(AEntity $entity): int
    {
        $data = $entity->toArray();
        $id   = $data['id'];
        unset($data['id']);

        if ($id == 0) {
            $this->db->insert($this->getTable($entity), $data);

            return $this->db->id();
        }

        $this->db->update($this->getTable($entity), $data, ['id' => $id]);

        return $id;
    }

    public function delete(AEntity $entity): bool
    {
        /** @var PDOStatement */
        $pdo = $this->db->delete($this->getTable($entity), ['id' => $entity->id]);

        if ($pdo->rowCount() == 1) {
            return true;
        }

        throw new \RuntimeException();
    }

    private function getTable(AEntity $entity): string
    {
        $reflect  = new \ReflectionClass($entity);

        return strtolower($reflect->getShortName()) . 's';
    }
}
