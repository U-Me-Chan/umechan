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
        $this->mphpd->connect();

        $data = $this->mphpd->player()->current_song();

        $this->mphpd->disconnect();

        if (!$data) {
            throw new RuntimeException();
        }

        return $data;
    }
}
