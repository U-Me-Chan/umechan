<?php

use Monolog\Logger;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Ridouchire\RadioMetrics\Collectors\IcecastCollector;
use Ridouchire\RadioMetrics\Collectors\MpdCollector;
use Ridouchire\RadioMetrics\Exceptions\EntityNotFound;
use Ridouchire\RadioMetrics\ICache;
use Ridouchire\RadioMetrics\SenderProvider;
use Ridouchire\RadioMetrics\Storage\Entites\Track;
use Ridouchire\RadioMetrics\Storage\RecordRepository;
use Ridouchire\RadioMetrics\Storage\TrackRepository;
use Ridouchire\RadioMetrics\TickHandler;
use Ridouchire\RadioMetrics\Utils\Md5Hash;

class TickHandlerTest extends TestCase
{
    private Logger|MockObject $logger;
    private MpdCollector|MockObject $mpd_collector;
    private IcecastCollector|MockObject $icecast_collector;
    private SenderProvider|MockObject $sender_provider;
    private TrackRepository|MockObject $track_repo;
    private RecordRepository|MockObject $record_repo;
    private Md5Hash|MockObject $md5hash;
    private ICache|MockObject $cache;
    private TickHandler $handler;

    public function setUp(): void
    {
        /** @var Logger|MockObject */
        $this->logger = $this->createMock(Logger::class);

        /** @var MpdCollector|MockObject */
        $this->mpd_collector = $this->createMock(MpdCollector::class);

        /** @var IcecastCollector|MockObject */
        $this->icecast_collector = $this->createMock(IcecastCollector::class);

        /** @var SenderProvider|MockObject */
        $this->sender_provider = $this->createMock(SenderProvider::class);

        /** @var TrackRepository|MockObject */
        $this->track_repo = $this->createMock(TrackRepository::class);

        /** @var RecordRepository|MockObject */
        $this->record_repo = $this->createMock(RecordRepository::class);

        /** @var Md5Hash|MockObject */
        $this->md5hash = $this->createMock(Md5Hash::class);

        /** @var ICache|MockObject */
        $this->cache = $this->createMock(ICache::class);

        $this->handler = new TickHandler(
            $this->logger,
            $this->mpd_collector,
            $this->icecast_collector,
            $this->sender_provider,
            $this->track_repo,
            $this->record_repo,
            $this->md5hash,
            $this->cache
        );
    }

    public function testMpdNoReturnCurrentTrack(): void
    {
        $this->mpd_collector->expects($this->once())->method('getData')->willThrowException(new RuntimeException());
        $this->logger->expects($this->atLeast(2))->method('debug');
        $this->logger->expects($this->once())->method('error');

        $this->handler->handle();
    }

    public function testTrackNotFound(): void
    {
        $this->mpd_collector->expects($this->once())->method('getData')->willReturn(['file' => '/var/lib/music/1.mp3']);
        $this->track_repo->expects($this->once())->method('findOne')->willThrowException(new EntityNotFound());
        $this->logger->expects($this->once())
            ->method('error')
            ->willReturnCallback(function (string $message) {
                $this->assertEquals('Трек не найден: /var/lib/music/1.mp3', $message);
            });

        $this->handler->handle();
    }

    public function testIcecastNoReturnData(): void
    {
        $this->mpd_collector->method('getData')->willReturn(['file' => '/var/lib/music/1.mp3']);
        $this->track_repo->method('findOne')->willReturn(Track::fromArray([
            'id'            => 1,
            'artist'        => 'Foo',
            'title'         => 'Bar',
            'first_playing' => time(),
            'last_playing'  => time(),
            'play_count'    => 1,
            'estimate'      => 10,
            'path'          => '/var/lib/music/1.mp3',
            'duration'      => 123,
            'mpd_track_id'  => 1,
            'hash'          => 'hash'
        ]));
        $this->cache->expects($this->atLeast(2))->method('set');
        $this->icecast_collector->expects($this->once())->method('getData')->willThrowException(new RuntimeException());
        $this->logger->expects($this->once())->method('error');

        $this->handler->handle();
    }

    public function testIncrementEstimate(): void
    {
        $this->mpd_collector->method('getData')->willReturn(['file' => '/var/lib/music/1.mp3']);
        $this->track_repo->method('findOne')->willReturn(Track::fromArray([
            'id'            => 1,
            'artist'        => 'Foo',
            'title'         => 'Bar',
            'first_playing' => time(),
            'last_playing'  => time(),
            'play_count'    => 1,
            'estimate'      => 10,
            'path'          => '/var/lib/music/1.mp3',
            'duration'      => 123,
            'mpd_track_id'  => 1,
            'hash'          => 'hash'
        ]));
        $this->icecast_collector->expects($this->once())->method('getData')->willReturn(['listeners' => 2]);
        $this->cache->expects($this->atLeast(3))->method('set');
        $this->cache->expects($this->once())->method('increment');

        $this->handler->handle();
    }

    public function testTrackWasChanged(): void
    {
        $this->mpd_collector->method('getData')->willReturn(['file' => '/var/lib/music/1.mp3']);
        $this->track_repo->method('findOne')->willReturn(Track::fromArray([
            'id'            => 1,
            'artist'        => 'Foo',
            'title'         => 'Bar',
            'first_playing' => time(),
            'last_playing'  => time(),
            'play_count'    => 1,
            'estimate'      => 10,
            'path'          => '/var/lib/music/1.mp3',
            'duration'      => 123,
            'mpd_track_id'  => 1,
            'hash'          => 'hash'
        ]));
        $this->icecast_collector->expects($this->atLeast(2))->method('getData')->willReturn(['listeners' => 2]);
        $this->cache->expects($this->atLeast(5))->method('set');
        $this->cache->expects($this->atLeast(2))->method('increment');

        $this->handler->handle();

        $this->track_repo->method('findOne')->willReturn(Track::fromArray([
            'id'            => 2,
            'artist'        => 'Blah',
            'title'         => 'Spam',
            'first_playing' => time(),
            'last_playing'  => time(),
            'play_count'    => 1,
            'estimate'      => 10,
            'path'          => '/var/lib/music/2.mp3',
            'duration'      => 123,
            'mpd_track_id'  => 1,
            'hash'          => 'hash'
        ]));

        $this->track_repo->expects($this->once())->method('save');
        $this->record_repo->expects($this->once())->method('save');

        $this->handler->handle();
    }
}
