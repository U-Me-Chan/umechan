<?php

namespace Ridouchire\RadioMetrics;

use Monolog\Logger;
use Ridouchire\RadioMetrics\Storage\Entites\Track;
use SplObjectStorage;

class SenderProvider
{
    public function __construct(
        private Logger $logger
    ) {
        $this->senders = new SplObjectStorage();
        $this->logger->debug("Инициализация провайдера отправителей оповещений");
    }

    public function attach(ISender $sender): void
    {
        $this->senders->attach($sender);

        $this->logger->info("Загружаю отправитель {$sender->getName()}");
    }

    public function detach(ISender $sender): void
    {
        $this->senders->detach($sender);

        $this->logger->info("Выгружаю отправитель {$sender->getName()}");
    }

    public function send(Track $track, int $listeners, string $additional = ''): void
    {
        $count = $this->senders->count();

        if ($count == 0) {
            $this->logger->info("Нет загруженных отправителей");
            return;
        }

        foreach ($this->senders as $sender) {
            try {
                $sender->send($track, $listeners, $additional);

                $this->logger->debug("Оповещение передано отправителю {$sender->getName()}");
            } catch (\RuntimeException $e) {
                $this->logger->error("Ошибка при работе отправителя {$sender->getName()}", ['error' => $e->getMessage()]);
            }
        }
    }
}
