<?php

use Monolog\Logger;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Ridouchire\RadioDbImporter\DirectoryIterator;
use Ridouchire\RadioDbImporter\FileManager;
use Ridouchire\RadioDbImporter\Handler;
use Ridouchire\RadioDbImporter\Id3v2Parser;
use Ridouchire\RadioDbImporter\Tracks\Track;
use Ridouchire\RadioDbImporter\Tracks\Track\Hash;
use Ridouchire\RadioDbImporter\Tracks\TrackRepository;

class HandlerTest extends TestCase
{
    private Handler $handler;
    private DirectoryIterator|MockObject $dir_iterator;
    private Id3v2Parser|MockObject $parser;
    private Logger|MockObject $logger;
    private TrackRepository|MockObject $track_repo;
    private FileManager|MockObject $file_manager;
    private string $tmp_mp3_file_path;
    private string $tmp_ogg_file_path;
    private string $dir_of_convertible_files;
    private string $dir_of_files_without_tags;

    public function setUp(): void
    {
        $tmp_dir = sys_get_temp_dir();

        $this->dir_of_convertible_files  = $tmp_dir . DIRECTORY_SEPARATOR . '/convert';
        $this->dir_of_files_without_tags = $tmp_dir . DIRECTORY_SEPARATOR . '/tagme';

        if (!is_dir($this->dir_of_convertible_files)) {
            mkdir($this->dir_of_convertible_files);
        }

        if (!is_dir($this->dir_of_files_without_tags)) {
            mkdir($this->dir_of_files_without_tags);
        }

        $this->tmp_mp3_file_path = $tmp_dir . DIRECTORY_SEPARATOR . '1.mp3';
        $this->tmp_ogg_file_path = $tmp_dir . DIRECTORY_SEPARATOR . '1.ogg';

        @touch($this->tmp_mp3_file_path);
        @touch($this->tmp_ogg_file_path);

        /** @var DirectoryIterator|MockObject*/
        $this->dir_iterator = $this->createMock(DirectoryIterator::class);

        /** @var Id3v2Parser|MockObject */
        $this->parser = $this->createMock(Id3v2Parser::class);

        /** @var Logger|MockObject */
        $this->logger = $this->createMock(Logger::class);

        /** @var TrackRepository|MockObject */
        $this->track_repo = $this->createMock(TrackRepository::class);

        $this->file_manager = new FileManager($this->dir_of_convertible_files, $this->dir_of_files_without_tags);

        $this->handler = new Handler(
            $this->dir_iterator,
            $this->parser,
            $this->logger,
            $this->track_repo,
            $this->file_manager
        );
    }

    public function testWithTrackIsNotMp3(): void
    {
        $path_to_ogg_file = $this->tmp_ogg_file_path;

        $this->dir_iterator->expects($this->once())
            ->method('getFile')
            ->willReturnCallback(function () use ($path_to_ogg_file) {
                /** @var SplFileObject|MockObject */
                $file = $this->getMockBuilder(SplFileObject::class)
                    ->setConstructorArgs(['php://memory'])
                    ->getMock();
                $file->method('getExtension')->willReturn('ogg');
                $file->method('getPathname')->willReturn($path_to_ogg_file);
                $file->method('getFilename')->willReturn('1.ogg');
                $file->method('isFile')->willReturn(true);

                return $file;
            });

        $this->track_repo->method('findOne')->willThrowException(new OutOfBoundsException());

        $this->logger->expects($this->once())
            ->method('info')
            ->willReturnCallback(fn (string $message) => $this->assertEquals('Трек не в MP3-формате, перемещаю в директорию для конвертации', $message));

        $this->dir_iterator->expects($this->once())->method('next');

        $h = $this->handler;

        $h();

        $this->assertTrue(file_exists($this->dir_of_convertible_files . DIRECTORY_SEPARATOR . '1.ogg'));
    }

    public function testWithTrackWithoutId3v2Tags(): void
    {
        $path_to_mp3_file = $this->tmp_mp3_file_path;

        $this->dir_iterator->expects($this->once())
            ->method('getFile')
            ->willReturnCallback(function () use ($path_to_mp3_file) {
                /** @var SplFileObject|MockObject */
                $file = $this->getMockBuilder(SplFileObject::class)
                    ->setConstructorArgs(['php://memory'])
                    ->getMock();
                $file->method('getExtension')->willReturn('mp3');
                $file->method('getPathname')->willReturn($path_to_mp3_file);
                $file->method('getFilename')->willReturn('1.mp3');
                $file->method('isFile')->willReturn(true);

                return $file;
            });

        $this->parser->expects($this->once())
            ->method('readFile')
            ->willReturnCallback(function (string $path) use ($path_to_mp3_file) {
                $this->assertEquals($path_to_mp3_file, $path);
            })->willThrowException(new InvalidArgumentException());

        $this->logger->expects($this->once())
            ->method('info')
            ->willReturnCallback(fn (string $message) => $this->assertEquals('Трек без тегов, перемещаю в директорию для разметки', $message));

        $this->dir_iterator->expects($this->once())->method('next');

        $h = $this->handler;

        $h();

        $this->assertTrue(file_exists($this->dir_of_files_without_tags . DIRECTORY_SEPARATOR . '1.mp3'));
    }

    public function testWithExistTrack(): void
    {
        $path_to_mp3_file = $this->tmp_mp3_file_path;

        $this->dir_iterator->expects($this->once())
            ->method('getFile')
            ->willReturnCallback(function () use ($path_to_mp3_file) {
                /** @var SplFileObject|MockObject */
                $file = $this->getMockBuilder(SplFileObject::class)
                    ->setConstructorArgs(['php://memory'])
                    ->getMock();
                $file->method('getExtension')->willReturn('mp3');
                $file->method('getPathname')->willReturn($path_to_mp3_file);
                $file->method('getFilename')->willReturn('1.mp3');
                $file->method('isFile')->willReturn(true);

                return $file;
            });

        $this->parser->expects($this->once())
            ->method('readFile')
            ->willReturnCallback(function (string $path) use ($path_to_mp3_file) {
                $this->assertEquals($path_to_mp3_file, $path);
            });

        $this->parser->method('getArtist')->willReturn('Foo');
        $this->parser->method('getTitle')->willReturn('Bar');
        $this->parser->method('getDuration')->willReturn(120);

        $this->track_repo->expects($this->once())
            ->method('findOne')
            ->willReturnCallback(function (array $filters) use ($path_to_mp3_file) {
                $this->assertArrayHasKey('hash', $filters);

                return new Track('Bar', 'Foo', 120, $path_to_mp3_file, Hash::fromString('foo'));
            });

        $this->track_repo->expects($this->once())
            ->method('update')
            ->willReturnCallback(function (Track $track) {
                $this->assertEquals('Foo', $track->getArtist());
                $this->assertEquals('Bar', $track->getTitle());
            });

        $this->logger->expects($this->exactly(3))
            ->method('info');

        $this->dir_iterator->expects($this->once())->method('next');

        $h = $this->handler;

        $h();
    }

    public function testWithNewTrack(): void
    {
        $tmp_file = $this->tmp_mp3_file_path;

        $this->dir_iterator->expects($this->once())
            ->method('getFile')
            ->willReturnCallback(function () use ($tmp_file){
                /** @var SplFileObject|MockObject */
                $file = $this->getMockBuilder(SplFileObject::class)
                    ->setConstructorArgs(['php://memory'])
                    ->getMock();
                $file->method('getExtension')->willReturn('mp3');
                $file->method('getPathname')->willReturn($tmp_file);
                $file->method('getFilename')->willReturn('1.mp3');
                $file->method('isFile')->willReturn(true);

                return $file;
            });

        $this->parser->expects($this->once())
            ->method('readFile')
            ->willReturnCallback(fn(string $path) => $this->assertEquals($tmp_file, $path));

        $this->parser->method('getArtist')->willReturn('Foo');
        $this->parser->method('getTitle')->willReturn('Bar');
        $this->parser->method('getDuration')->willReturn(120);

        $this->track_repo->expects($this->once())
            ->method('findOne')
            ->willThrowException(new  OutOfBoundsException());

        $this->track_repo->expects($this->once())
            ->method('save')
            ->willReturn(1);

        $this->logger->expects($this->once())
            ->method('info')
            ->willReturnCallback(fn(string $message) => $this->assertEquals('Трек #1 добавлен по пути ' . $tmp_file, $message));

        $h = $this->handler;

        $h();
    }
}
