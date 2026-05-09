<?php

namespace Ridouchire\RadioDbImporter\HandleSteps;

use Monolog\Logger;
use RuntimeException;
use React\Promise\Promise;
use React\Promise\PromiseInterface;
use Ridouchire\RadioDbImporter\HandlerData;
use Ridouchire\RadioDbImporter\HandleStep;

class CheckFileIsFileStep implements HandleStep
{
    public function __construct(
        private Logger $logger
    ) {
    }

    public function process(HandlerData $data): PromiseInterface
    {
        return new Promise(function ($resolve, $reject) use ($data) {
            if ($data->file->isFile()) {
                $this->logger->debug(self::class . ": Путь является файлом: {$data->file->getPathname()}");

                $resolve($data);
            } else {
                $reject(new RuntimeException(self::class . ": Не является файлом: {$data->file->getPathname()}"));
            }
        });
    }
}
