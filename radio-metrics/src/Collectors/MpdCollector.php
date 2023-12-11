<?php

namespace Ridouchire\RadioMetrics\Collectors;

use FloFaber\MphpD\MphpD;
use Ridouchire\RadioMetrics\ICollector;
use RuntimeException;

class MpdCollector implements ICollector
{
    private MphpD $mphpd;

    public function __construct(
        private string $hostname,
        private int $port,
        private int $timeout = 5
    ) {
        $this->mphpd = new MphpD([
            'host'    => $hostname,
            'port'    => $port,
            'timeout' => $timeout
        ]);
    }

    public function getData(): array
    {
        if (!$this->mphpd->connected) {
            $this->mphpd->connect();
        }

        $data = $this->mphpd->player()->current_song();

        if (!$data) {
            throw new RuntimeException();
        }

        return $data;
    }
}
