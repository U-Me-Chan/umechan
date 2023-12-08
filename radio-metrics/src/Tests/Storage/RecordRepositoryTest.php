<?php

namespace Ridouchire\RadioMetrics\Tests\Storage;

use InvalidArgumentException;
use Ridouchire\RadioMetrics\Exceptions\EntityNotFound;
use Ridouchire\RadioMetrics\Storage\Entites\Record;
use Ridouchire\RadioMetrics\Storage\RecordRepository;
use Ridouchire\RadioMetrics\Tests\DatabaseTestCase;
use RuntimeException;

class RecordRepositoryTest extends DatabaseTestCase
{
    private RecordRepository $repo;

    public function setUp(): void
    {
        parent::setUp();

        $this->repo = new RecordRepository($this->db);
    }

    public function testSave(): void
    {
        $record = Record::draft(1, 1);

        $id = $this->repo->save($record);

        $this->assertIsInt($id);
    }

    public function testFindOne(): void
    {
        $record = $this->repo->findOne(['id' => 1]);

        $this->assertInstanceOf(Record::class, $record);

        $this->assertEquals(1, $record->getId());

        $this->expectException(EntityNotFound::class);

        $this->repo->findOne(['id' => 2]);

        $this->expectException(InvalidArgumentException::class);

        $this->repo->findOne();
    }

    public function testFindMany(): void
    {
        list($records, $count) = $this->repo->findMany();

        $this->assertEquals(1, $count);
        $this->assertInstanceOf(Record::class, $records[0]);

        list(,$count) = $this->repo->findMany(['track_id' => 1]);

        $this->assertEquals(1, $count);

        list($records, $count) = $this->repo->findMany(['offset' => 1]);

        $this->assertEquals(1, $count);
        $this->assertCount(0, $records);
    }

    public function testDelete(): void
    {
        $record = Record::fromArray([
            'id'        => 1,
            'track_id'  => 1,
            'timestamp' => time(),
            'listeners' => 1
        ]);

        $this->assertTrue($this->repo->delete($record));

        $this->expectException(InvalidArgumentException::class);

        $this->repo->delete(Record::draft(1, 1));

        $this->expectException(RuntimeException::class);

        $record = Record::fromArray([
            'id'        => 2,
            'track_id'  => 1,
            'timestamp' => time(),
            'listeners' => 1
        ]);

        $this->repo->delete($record);
    }
}
