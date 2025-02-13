<?php

namespace Ridouchire\RadioDbImporter;

use InvalidArgumentException;
use OutOfBoundsException;
use SplFileInfo;
use Monolog\Logger;
use Ridouchire\RadioDbImporter\Tracks\TrackRepository;
use Ridouchire\RadioDbImporter\Tracks\Track;
use Ridouchire\RadioDbImporter\Tracks\Track\Hash;
use Ridouchire\RadioDbImporter\Utils\PathCutter;
use Throwable;

final class Handler
{
    public function __construct(
        private DirectoryIterator $dir_iterator,
        private Id3v2Parser $tags_parser,
        private Logger $logger,
        private TrackRepository $track_repo,
        private FileManager $file_manager,
        private PathCutter $path_cutter
    ) {
    }

    public function __invoke(): void
    {
        /** @var SplFileInfo */
        $file = $this->dir_iterator->getFile();

        if (!$file->isFile()) {
            $this->dir_iterator->next();

            return;
        }

        $this->logger->debug('Получен файл: ' . $file->getPathname());

        if ($file->getExtension() !== 'mp3') {
            $this->logger->info('Трек не в MP3-формате, перемещаю в директорию для конвертации');

            try {
                $track = $this->track_repo->findOne(['hash' => Hash::fromPath($file->getPathname())->toString()]);

                $this->track_repo->delete($track);
            } catch (OutOfBoundsException) {
            } catch (Throwable) {
                $track = new Track('', '', 0, $file->getPathname(), Hash::fromPath($file->getPathname()));

                $this->track_repo->delete($track);

                //FIXME: треки без тегов в БД вызывают падение
            }

            $this->file_manager->moveToDirOfConvertibleFiles($file->getPathname(), $file->getFilename());

            $this->dir_iterator->next();

            return;
        }

        try {
            $this->tags_parser->readFile($file->getPathname());
        } catch (InvalidArgumentException) {
            $this->logger->info('Трек без тегов, перемещаю в директорию для разметки');

            try {
                $track = $this->track_repo->findOne(['hash' => Hash::fromPath($file->getPathname())->toString()]);

                $this->track_repo->delete($track);
            } catch (OutOfBoundsException) {
            }

            $this->file_manager->moveToDirOfFilesWithoutTags($file->getPathname(), $file->getFilename());

            $this->dir_iterator->next();

            return;
        }

        try {
            $track = $this->track_repo->findOne(['hash' => Hash::fromPath($file->getPathname())->toString()]);

            if ($track->getArtist() !== $this->tags_parser->getArtist()) {
                $track->artist = $this->tags_parser->getArtist();

                $this->logger->info('У трека обновлён исполнитель');
            }

            if ($track->getTitle() !== $this->tags_parser->getTitle()) {
                $track->title = $this->tags_parser->getTitle();

                $this->logger->info('У трека обновлено наименование');
            }

            if ($track->getPath() !== $this->path_cutter->cut($file->getPathname())) {
                $track->path = $this->path_cutter->cut($file->getPathname());

                $this->logger->info('У трека обновлён путь');
            }

            if ($track->isUpdated()) {
                $this->track_repo->update($track);

                $this->logger->info('Обновлён трек ' . $file->getPathname());
            }

            $this->dir_iterator->next();

            return;
        } catch (OutOfBoundsException) {
            $track = new Track(
                $this->tags_parser->getArtist(),
                $this->tags_parser->getTitle(),
                $this->tags_parser->getDuration(),
                $this->path_cutter->cut($file->getPathname()),
                Hash::fromPath($file->getPathname())
            );

            $id = $this->track_repo->save($track);

            $this->logger->info('Трек #' . $id . ' добавлен по пути ' . $file->getPathname());

            $this->dir_iterator->next();
        }
    }
}
