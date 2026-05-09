<?php

use Monolog\Logger;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Ridouchire\RadioDbImporter\FileManager;
use Ridouchire\RadioDbImporter\HandlerData;
use Ridouchire\RadioDbImporter\HandleSteps\CheckTrackEstimateStep;
use Ridouchire\RadioDbImporter\Tracks\Services\TrackEstimateValidator;
use Ridouchire\RadioDbImporter\Tracks\Track;
use Ridouchire\RadioDbImporter\Tracks\Track\Hash;
use Ridouchire\RadioDbImporter\Tracks\TrackRepository;

class CheckTrackEstimateStepTest extends TestCase
{
    private CheckTrackEstimateStep $check_track_estimate_step;
    private MockObject|FileManager $file_manager;
    private MockObject|TrackRepository $track_repository;

    public function setUp(): void
    {
        $this->file_manager     = $this->createMock(FileManager::class);
        $this->track_repository = $this->createMock(TrackRepository::class);

        $this->check_track_estimate_step = new CheckTrackEstimateStep(
            new TrackEstimateValidator(5),
            $this->track_repository,
            $this->file_manager,
            $this->createMock(Logger::class)
        );
    }

    #[Test]
    public function attemptTrackWithBadEstimate(): void
    {
        $file = $this->getMockBuilder(SplFileObject::class)
            ->setConstructorArgs(['php://memory'])
            ->getMock();
        $file->method('getPathname')->willReturn('/tmp/Pop/1.mp3');
        $file->method('getFilename')->willReturn('1.mp3');

        $this->file_manager->expects($this->once())
            ->method('moveToDirOfNegativeEstimate')
            ->willReturnCallback(function (string $filepath, string $filename) {
                $this->assertEquals('/tmp/Pop/1.mp3', $filepath);
                $this->assertEquals('1.mp3', $filename);

                return '';
            });

        $this->file_manager->expects($this->once())
            ->method('getRelativeFilepath')
            ->willReturn('Pop/1.mp3');

        $this->track_repository->expects($this->once())
            ->method('update');

        $track = new Track(
            artist: 'Foo',
            title: 'Bar',
            duration: 120,
            path: '/tmp/Pop/1.mp3',
            hash: Hash::fromString('blah-blah'),
            estimate: -1250,
            play_count: 10
        );

        $this->check_track_estimate_step->process(new HandlerData($file, $track))
            ->catch(function ($error) {
                $this->assertInstanceOf(RuntimeException::class, $error);
            });
    }

    #[Test]
    public function attemptTrackWithGoodEstimate(): void
    {
        $file = $this->getMockBuilder(SplFileObject::class)
            ->setConstructorArgs(['php://memory'])
            ->getMock();
        $file->method('getPathname')->willReturn('/tmp/Pop/1.mp3');
        $file->method('getFilename')->willReturn('1.mp3');

        $track = new Track(
            artist: 'Foo',
            title: 'Bar',
            duration: 120,
            path: '/tmp/Pop/1.mp3',
            hash: Hash::fromString('blah-blah'),
            estimate: 120,
            play_count: 10
        );

        $this->check_track_estimate_step->process(new HandlerData($file, $track))
            ->then(function ($data) {
                $this->assertInstanceOf(HandlerData::class, $data);
            });
    }
}
