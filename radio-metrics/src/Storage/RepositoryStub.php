<?php

namespace Ridouchire\RadioMetrics\Storage;

use Ridouchire\RadioMetrics\Storage\AEntity;
use Ridouchire\RadioMetrics\Storage\Entites\Record;
use Ridouchire\RadioMetrics\Storage\Entites\Track;

class RepositoryStub
{
    public function findOne(AEntity $entity) // костыль, но что поделать
    {
        if ($entity instanceof Track) {
            throw new \RuntimeException();
        }

        if ($entity instanceof Record) {
            return Record::draft(1, 1);
        }

        throw new \RuntimeException();
    }

    public function save(): int
    {
        return 1;
    }

    public function delete(): bool
    {
        return true;
    }
}
