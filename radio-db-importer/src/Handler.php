<?php

namespace Ridouchire\RadioDbImporter;

use RuntimeException;
use SplFileInfo;
use Monolog\Logger;
use Ridouchire\RadioDbImporter\Exceptions\DirectoryIsEndException;

final class Handler
{
    public function __construct(
        private Logger $logger,
        private DirectoryIterator $dir_iterator,
        private HandlerStepsChain $pipeline
    ) {
    }

    /**
     * См. ссылку для понимания возвращаемого типа
     *
     * @see React\EventLoop\Loop:addPeriodicTimer()
     */
    public function __invoke(): void
    {
        try {
            /** @var SplFileInfo */
            $file = $this->dir_iterator->getFile();

            $this->logger->debug("Текущий путь: {$file->getPathname()}");
        } catch (RuntimeException) {
            $this->logger->info('Директория кончилась');

            throw new DirectoryIsEndException();
        }

        $this->pipeline
            ->process(new HandlerData($file))
            ->then(function (HandlerData $data) {
                $this->logger->debug(self::class . ": файл успешно обработан: {$data->file->getPathname()}");
                $this->logger->debug(self::class . ": Двигаюсь к следующему файлу");

                $this->dir_iterator->next();
            }, function (\Throwable $e) {
                $this->logger->debug(self::class . ": неуспешно обработан файл");
                $this->logger->error($e->getMessage());
                $this->logger->debug(self::class . ": Двигаюсь к следующему файлу");

                $this->dir_iterator->next();
            });
    }
}
