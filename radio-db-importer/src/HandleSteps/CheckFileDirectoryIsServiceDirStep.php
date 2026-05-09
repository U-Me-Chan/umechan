<?php

namespace Ridouchire\RadioDbImporter\HandleSteps;

use RuntimeException;
use Monolog\Logger;
use React\Promise\Promise;
use React\Promise\PromiseInterface;
use Ridouchire\RadioDbImporter\FileManager;
use Ridouchire\RadioDbImporter\HandlerData;
use Ridouchire\RadioDbImporter\HandleStep;

class CheckFileDirectoryIsServiceDirStep implements HandleStep
{
    public function __construct(
        private FileManager $file_manager,
        private Logger $logger
    ) {
    }

    public function process(HandlerData $data): PromiseInterface
    {
        return new Promise(function ($resovle, $reject) use ($data) {
            if ($this->file_manager->isDuplicateDir($data->file->getPathname()) ||
                $this->file_manager->isNegativeDir($data->file->getPathname())
            ) {
                $reject(new RuntimeException(self::class . ": Файл в сервисной директории: {$data->file->getPathname()}"));
            } else {
                $this->logger->debug(self::class . "файл не в сервисной директории: {$data->file->getPathname()}");

                $resovle($data);
            }
        });
    }
}
