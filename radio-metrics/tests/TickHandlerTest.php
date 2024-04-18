<?php

use Monolog\Logger;
use PHPUnit\Framework\MockObject\MockObject;
use Ridouchire\RadioMetrics\Collectors\IcecastCollector;
use Ridouchire\RadioMetrics\Collectors\MpdCollector;
use Ridouchire\RadioMetrics\SenderProvider;
use Ridouchire\RadioMetrics\Senders\DummySender;
use Ridouchire\RadioMetrics\Storage\Entites\Record;
use Ridouchire\RadioMetrics\Storage\Entites\Track;
use Ridouchire\RadioMetrics\Storage\RecordRepository;
use Ridouchire\RadioMetrics\Storage\TrackRepository;
use Ridouchire\RadioMetrics\Tests\DatabaseTestCase;
use Ridouchire\RadioMetrics\TickHandler;
use Ridouchire\RadioMetrics\Utils\Container;
use Ridouchire\RadioMetrics\Utils\Md5Hash;

class TickHandlerTest extends DatabaseTestCase
{
    private TrackRepository $trackRepo;
    private RecordRepository $recordRepo;
    private TickHandler $tickHandler;

    public function setUp(): void
    {
        parent::setUp();

        $logger = $this->createMock(Logger::class);

        /** @var MpdCollector|MockObject */
        $mpdCollector = $this->createMock(MpdCollector::class);
        $mpdCollector->method('getData')->willReturn([
            'artist' => 'Foo',
            'title' => 'Bar',
            'file'  => 'Music/Non Stop/foo - bar.mp3',
            'time'  => 123,
            'id'    => 1244
        ]);

        /** @var IcecastCollector|MockObject */
        $icecastCollector = $this->createMock(IcecastCollector::class);
        $icecastCollector->method('getData')->willReturn([
            'title' => 'Foo - Bar',
            'listeners' => 1
        ]);

        /** @var Md5Hash|MockObject */
        $md5hash = $this->createMock(Md5Hash::class);
        $md5hash->method('get')->willReturn(md5('test'));

        $senderProvider = new SenderProvider($logger);
        $senderProvider->attach(new DummySender());
        $this->trackRepo = new TrackRepository($this->db);
        $this->recordRepo = new RecordRepository($this->db);

        $this->tickHandler = new TickHandler(
            $logger,
            $mpdCollector,
            $icecastCollector,
            $senderProvider,
            $this->trackRepo,
            $this->recordRepo,
            $md5hash,
            new Container()
        );
    }

    public function test(): void
    {
        $c = $this->tickHandler;

        $c();

        /** @var Track */
        $track = $this->trackRepo->findOne(['id' => 2]);

        $this->assertEquals('Foo', $track->getArtist());
        $this->assertEquals('Bar', $track->getTitle());
        $this->assertEquals('Music/Non Stop/foo - bar.mp3', $track->getPath());
        $this->assertEquals(123, $track->getDuration());
        $this->assertEquals(1244, $track->getMpdTrackId());
        $this->assertEquals(1, $track->getPlayCount());

        /** @var Record */
        $record = $this->recordRepo->findOne(['id' => 2]);

        $this->assertEquals(2, $record->getTrackId());
        $this->assertEquals(1, $record->getListeners());
    }
}
