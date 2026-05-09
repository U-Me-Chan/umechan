<?php

namespace Ridouchire\RadioDbImporter\HandleSteps;

use Monolog\Logger;
use React\Promise\Promise;
use React\Promise\PromiseInterface;
use Ridouchire\RadioDbImporter\FileManager;
use Ridouchire\RadioDbImporter\HandlerData;
use Ridouchire\RadioDbImporter\HandleStep;
use Ridouchire\RadioDbImporter\Tracks\TrackRepository;

class CheckTrackPathStep implements HandleStep
{
    public function __construct(
        private FileManager $file_manager,
        private TrackRepository $track_repository,
        private Logger $logger
    ) {
    }

    public function process(HandlerData $data): PromiseInterface
    {
        return new Promise(function ($resolve, $reject) use ($data) {
            try {
                $current_relative_path = $this->file_manager->getRelativeFilepath($data->file->getPathname());

                $this->logger->debug(self::class . ": Сверяю путь текущего файла с существующим путём в БД: {$data->file->getPathname()}");

                if ($current_relative_path !== $data->track->getPath()) {
                    $previous_absolute_pathfile = $this->file_manager->getAbsoluteFilepath($data->track->getPath());

                    if ($this->file_manager->isFileExist($previous_absolute_pathfile)) {
                        $this->file_manager->removeFile($previous_absolute_pathfile);

                        $this->logger->info(self::class . ': Удалён дубликат трека: ' . $previous_absolute_pathfile);
                    }

                    /** @phpstan-ignore property.private */
                    $data->track->path = $current_relative_path;

                    $this->track_repository->update($data->track);

                    $this->logger->info(self::class . ': У трека был обновлён путь: ' . $data->track->getPath());
                }

                $resolve($data);
            } catch (\Throwable $e) {
                $reject($e);
            }
        });
    }
}
