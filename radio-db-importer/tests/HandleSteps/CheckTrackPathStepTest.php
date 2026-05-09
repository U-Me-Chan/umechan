<?php

use Monolog\Logger;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Ridouchire\RadioDbImporter\FileManager;
use Ridouchire\RadioDbImporter\HandlerData;
use Ridouchire\RadioDbImporter\HandleSteps\CheckTrackPathStep;
use Ridouchire\RadioDbImporter\Tracks\Track;
use Ridouchire\RadioDbImporter\Tracks\Track\Hash;
use Ridouchire\RadioDbImporter\Tracks\TrackRepository;

class CheckTrackPathStepTest extends TestCase
{
    private MockObject|TrackRepository $track_repository;
    private MockObject|Logger $logger;
    private FileManager $file_manager;
    private CheckTrackPathStep $check_track_path_step;

    public function setUp(): void
    {
        $this->track_repository = $this->createMock(TrackRepository::class);
        $this->logger           = $this->createMock(Logger::class);
        $this->file_manager     = new FileManager(
            '/convert',
            '/tagme',
            '/tmp/BadEstimate',
            '/tmp'
        );

        $this->check_track_path_step = new CheckTrackPathStep(
            $this->file_manager,
            $this->track_repository,
            $this->logger
        );
    }

    #[Test]
    public function attemptRunWithoutChangeTrackPath(): void
    {
        /** @var MockObject|SplFileInfo */
        $file = $this->getMockBuilder(SplFileObject::class)
            ->setConstructorArgs(['php://memory'])
            ->getMock();
        $file->method('getPathname')
            ->willReturn('/tmp/Pop/1.mp3');

        $track = new Track(
            artist: 'Foo',
            title: 'Bar',
            duration: 120,
            path: 'Pop/1.mp3',
            hash: Hash::fromString('blah-blah')
        );

        $this->logger->expects($this->never())->method('info');
        $this->track_repository->expects($this->never())->method('update');

        $data = new HandlerData($file, $track);

        $this->check_track_path_step->process($data)
            ->then(function (HandlerData $data) {
                $this->assertInstanceOf(HandlerData::class, $data);
            });
    }

    #[Test]
    public function attemptRunWithTrackPathChanged(): void
    {
        /** @var MockObject|SplFileInfo */
        $file = $this->getMockBuilder(SplFileObject::class)
            ->setConstructorArgs(['php://memory'])
            ->getMock();
        $file->method('getPathname')
            ->willReturn('/tmp/Pop Dance/1.mp3');

        $track = new Track(
            artist: 'Foo',
            title: 'Bar',
            duration: 120,
            path: 'Pop/1.mp3',
            hash: Hash::fromString('blah-blah')
        );

        $this->logger->expects($this->once())->method('info');

        $this->track_repository->expects($this->once())
            ->method('update')
            ->willReturnCallback(function (Track $track) {
                $this->assertEquals('Pop Dance/1.mp3', $track->getPath());
            });

        $data = new HandlerData($file, $track);

        $this->check_track_path_step->process($data)
            ->then(function (HandlerData $data) {
                $this->assertInstanceOf(HandlerData::class, $data);
            });
    }

    #[Test]
    public function attemptRunWithTrackHasDuplicate(): void
    {
        /** @var MockObject|SplFileInfo */
        $file = $this->getMockBuilder(SplFileObject::class)
            ->setConstructorArgs(['php://memory'])
            ->getMock();
        $file->method('getPathname')
            ->willReturn('/tmp/Pop Dance/1.mp3');

        $track = new Track(
            artist: 'Foo',
            title: 'Bar',
            duration: 120,
            path: 'Pop/1.mp3',
            hash: Hash::fromString('blah-blah')
        );

        $this->logger->expects($this->exactly(2))->method('info');

        $this->track_repository->expects($this->once())
            ->method('update')
            ->willReturnCallback(function (Track $track) {
                $this->assertEquals('Pop Dance/1.mp3', $track->getPath());
            });

        $data = new HandlerData($file, $track);

        $file_manager = $this->createMock(FileManager::class);
        $file_manager->expects($this->once())
            ->method('isFileExist')
            ->willReturn(true);
        $file_manager->expects($this->once())
            ->method('removeFile')
            ->willReturn(true);
        $file_manager->expects($this->once())
            ->method('getRelativeFilepath')
            ->willReturn('Pop Dance/1.mp3');
        $file_manager->expects($this->once())
            ->method('getAbsoluteFilepath')
            ->willReturn('/tmp/Pop/1.mp3');

        $check_track_path_step = new CheckTrackPathStep(
            $file_manager,
            $this->track_repository,
            $this->logger
        );

        $check_track_path_step->process($data)
            ->then(function (HandlerData $data) {
                $this->assertInstanceOf(HandlerData::class, $data);
            });
    }
}
