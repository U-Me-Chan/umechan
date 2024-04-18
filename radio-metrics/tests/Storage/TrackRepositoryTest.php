<?php

use Ridouchire\RadioMetrics\Exceptions\EntityNotFound;
use Ridouchire\RadioMetrics\Storage\Entites\Record;
use Ridouchire\RadioMetrics\Storage\Entites\Track;
use Ridouchire\RadioMetrics\Storage\TrackRepository;
use Ridouchire\RadioMetrics\Tests\DatabaseTestCase;

class TrackRepositoryTest extends DatabaseTestCase
{
    private TrackRepository $repo;
    private string $hash = '';

    public function setUp(): void
    {
        parent::setUp();

        $this->repo = new TrackRepository($this->db);
        $this->hash = md5('test');
    }

    public function testSave(): void
    {
        $track = Track::draft('Foo', 'Bar', $this->hash, 'Music/Non Stop', 123, 1122);

        $id = $this->repo->save($track);

        $this->assertIsInt($id);
        $this->assertEquals(1, $id);
    }

    public function testFindOne(): void
    {
        /** @var Track */
        $track = $this->repo->findOne(['id' => 1]);

        $this->assertInstanceOf(Track::class, $track);

        $this->assertEquals(1, $track->getId());
        $this->assertEquals('Foo', $track->getArtist());
        $this->assertEquals('Bar', $track->getTitle());
        $this->assertEquals(123, $track->getDuration());
        $this->assertEquals('Music/Non Stop', $track->getPath());
        $this->assertEquals(1122, $track->getMpdTrackId());
        $this->assertEquals($this->hash, $track->getHash());

        $this->expectException(EntityNotFound::class);

        $this->repo->findOne(['id' => 2]);

        $this->expectException(InvalidArgumentException::class);

        $this->repo->findOne();
        $this->repo->findOne(['test' => 'shit']);
    }

    public function testFindMany(): void
    {
        list($tracks, $count) = $this->repo->findMany();

        $this->assertIsArray($tracks);
        $this->assertInstanceOf(Track::class, $tracks[0]);
        $this->assertIsInt($count);
        $this->assertEquals(1, $count);

        list(, $count) = $this->repo->findMany(['artist' => 'Spam']);

        $this->assertEquals(0, $count);

        list(, $count) = $this->repo->findMany(['artist' => 'F']);

        $this->assertEquals(1, $count);

        list(, $count) = $this->repo->findMany(['title' => 'a']);

        $this->assertEquals(1, $count);

        list($tracks, $count) = $this->repo->findMany(['offset' => 1]);

        $this->assertCount(0, $tracks);
        $this->assertEquals(1, $count);
    }

    public function testDelete(): void
    {
        $this->assertTrue($this->repo->delete(Track::fromArray([
            'id' => 1,
            'artist' => 'Foo',
            'title'  => 'Bar',
            'first_playing' => time(),
            'last_playing'  => time(),
            'play_count'    => 1,
            'estimate'      => 1,
            'path'          => 'Music/Non Stop',
            'duration'      => 123,
            'mpd_track_id'  => 1122,
            'hash'          => 'test'
        ])));

        $this->expectException(InvalidArgumentException::class);

        $this->repo->delete(Track::draft('', '', 'test', 'test'));
        $this->repo->delete(Record::draft(1, 1));
    }
}
