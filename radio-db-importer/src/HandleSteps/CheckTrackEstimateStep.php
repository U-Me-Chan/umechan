<?php

namespace Ridouchire\RadioDbImporter\HandleSteps;

use Monolog\Logger;
use RuntimeException;
use React\Promise\Promise;
use React\Promise\PromiseInterface;
use Ridouchire\RadioDbImporter\FileManager;
use Ridouchire\RadioDbImporter\HandlerData;
use Ridouchire\RadioDbImporter\HandleStep;
use Ridouchire\RadioDbImporter\Tracks\Services\TrackEstimateValidator;
use Ridouchire\RadioDbImporter\Tracks\TrackRepository;

class CheckTrackEstimateStep implements HandleStep
{
    public function __construct(
        private TrackEstimateValidator $track_estimate_validator,
        private TrackRepository $track_repository,
        private FileManager $file_manager,
        private Logger $logger
    ) {
    }

    public function process(HandlerData $data): PromiseInterface
    {
        return new Promise(function ($resolve, $reject) use ($data) {
            try {
                if ($this->track_estimate_validator->isBadEstimate($data->track)) {
                    $new_path = $this->file_manager->moveToDirOfNegativeEstimate(
                        $data->file->getPathname(),
                        $data->file->getFilename()
                    );
                    $new_path = $this->file_manager->getRelativeFilepath($new_path);

                    /** @phpstan-ignore property.private */
                    $data->track->path = $new_path;

                    $this->track_repository->update($data->track);

                    $reject(new RuntimeException(self::class . ': Трек был перемещён в директорию с отрицательными оценками: ' . $data->track->getPath()));
                } else {
                    $this->logger->debug(self::class . ": Трек имеет положительную оценку: {$data->file->getPathname()}");

                    $resolve($data);
                }
            } catch (\Throwable $e) {
                $reject($e);
            }
        });
    }
}
