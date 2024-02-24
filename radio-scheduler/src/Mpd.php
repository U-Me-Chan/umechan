<?php

namespace Ridouchire\RadioScheduler;

use FloFaber\MphpD\MphpD;
use FloFaber\MPDException;
use FloFaber\MphpD\Filter;
use Monolog\Logger;

class Mpd
{
    private MphpD $mphpd;

    public function __construct(
        private Logger $log,
        string $hostname,
        int $port
    ) {
        $this->mphpd = new Mphpd([
            'host' => $hostname,
            'port' => $port,
            'timeout' => 10
        ]);
    }

    /**
     * Добавляет в очередь файл/директорию по указанному относительному пути
     *
     * @param string $uri Относительный путь до файла/директории
     *
     * @return bool
     */
    public function addToQueue(string $uri): bool
    {
        return $this->getConnection()->queue()->add($uri);
    }

    /**
     * Очищает очередь воспроизведения, оставляя текущий трек
     *
     * @return bool
     */
    public function cropQueue(): bool
    {
        /** @var array */
        $queued_songs = $this->getConnection()->queue()->search(new Filter('file', 'contains', '/'));

        if ($queued_songs == false) {
            return false;
        }

        if (sizeof($queued_songs) == 0) {
            return false;
        }

        /** @var int */
        $current_song_position = $this->getConnection()->player()->current_song()['pos'];

        /** @var bool */
        $res = $this->getConnection()->queue()->move($current_song_position, 0);

        if (!$res) {
            throw new \RuntimeException("MPD: ошибка при перемещении текущего трека в начало очереди");
        }

        $res = $this->getConnection()->queue()->delete([1, sizeof($queued_songs)]);

        if (!$res) {
            throw new \RuntimeException("MPD: ошибка очистки очереди");
        }

        return $res;
    }

    private function getConnection(): MphpD
    {
        if (!$this->mphpd->connected) {
            try {
                $this->mphpd->connect();
            } catch (MPDException $e) {
                $this->log->error($e->getMessage(), ['error' => $this->mphpd->get_last_error()]);
            }
        }

        return $this->mphpd;
    }
}
