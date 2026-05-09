<?php

namespace Ridouchire\RadioDbImporter\HandleSteps;

use Monolog\Logger;
use OutOfBoundsException;
use React\Promise\Promise;
use React\Promise\PromiseInterface;
use Ridouchire\RadioDbImporter\Exceptions\ATagParserException;
use Ridouchire\RadioDbImporter\FileManager;
use Ridouchire\RadioDbImporter\HandlerData;
use Ridouchire\RadioDbImporter\HandleStep;
use Ridouchire\RadioDbImporter\Id3v2Parser;
use Ridouchire\RadioDbImporter\Tracks\Track;
use Ridouchire\RadioDbImporter\Tracks\Track\Hash;
use Ridouchire\RadioDbImporter\Tracks\TrackRepository;
use RuntimeException;

class RetrieveTrackFromDatabaseStep implements HandleStep
{
    public function __construct(
        private TrackRepository $track_repository,
        private Id3v2Parser $tag_parser,
        private FileManager $file_manager,
        private Logger $logger
    ) {
    }

    public function process(HandlerData $data): PromiseInterface
    {
        return new Promise(function ($resolve, $reject) use ($data) {
            $this->logger->debug(self::class . ": начинаю поиск файла в БД по его хешу: {$data->file->getPathname()}");

            try {
                $data->track = $this->track_repository->findOne([
                    'hash' => Hash::fromPath($data->file->getPathname())->toString()
                ]);

                $this->logger->debug(self::class . ": файл найден по хешу: {$data->track->getHash()}");

                $resolve($data);
            } catch (OutOfBoundsException) {
                $this->logger->debug(self::class . ": файл не найден по хешу: {$data->file->getPathname()}");
                $this->logger->debug(self::class . ": читаю IDv2-теги файла: {$data->file->getPathname()}");

                $this->tag_parser->readFile($data->file->getPathname());

                $this->logger->debug(self::class . ": IDv2-теги прочитаны у файла: {$data->file->getPathname()}");

                $track = new Track(
                    artist: $this->tag_parser->getArtist(),
                    title: $this->tag_parser->getTitle(),
                    duration: $this->tag_parser->getDuration(),
                    path: $this->file_manager->getRelativeFilepath($data->file->getPathname()),
                    hash: Hash::fromPath($data->file->getPathname())
                );

                $id = $this->track_repository->save($track);

                $data->track = $track;

                $this->logger->info(self::class . ": новый трек записан в БД под номером #{$id}: {$data->file->getPathname()}");

                $resolve($data);
            } catch (ATagParserException $e) {
                $this->logger->error(self::class . ": ошибка чтения IDv2-тегов файла: {$data->file->getPathname()}");

                $reject($e);
            } catch (\Throwable $e) {
                $reject($e);
            }
        });
    }
}
