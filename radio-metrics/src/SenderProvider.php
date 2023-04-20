<?php

namespace Ridouchire\RadioMetrics;

use Monolog\Logger;
use Ridouchire\RadioMetrics\Storage\Entites\Track;
use SplObjectStorage;

class SenderProvider implements ISender
{
    public function __construct(
        private Logger $logger
    ) {
        $this->senders = new SplObjectStorage();
    }

    public function attach(ISender $sender): void
    {
        $this->senders->attach($sender);
    }

    public function detach(ISender $sender): void
    {
        $this->senders->detach($sender);
    }

    public function send(Track $track, int $listeners, string $additional = ''): void
    {
        $count = $this->senders->count();

        if ($count == 0) {
            return;
        }

        foreach ($this->senders as $sender) {
            try {
                $sender->send($track, $listeners, $additional);
            } catch (\RuntimeException $e) {
                $this->logger->error($e->getMessage());
            }
        }
    }
}
