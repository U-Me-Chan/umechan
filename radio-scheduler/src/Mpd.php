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
        $queue_count = $this->getQueueCount();

        if ($queue_count == 0) {
            $this->log->info('MPD: очередь пуста');

            return false;
        }

        /** @var array|false */
        $current_song_data = $this->getConnection()->player()->current_song();

        if ($current_song_data == false) {
            $this->log->info('MPD: нет воспроизводимого трека');
        }

        /** @var int */
        $current_song_position = $current_song_data['pos'];

        /** @var bool */
        $res = $this->getConnection()->queue()->move($current_song_position, 0);

        $this->log->debug('MPD: Перемещаю текущий трек в начало очереди');

        if (!$res) {
            throw new \RuntimeException("MPD: ошибка при перемещении текущего трека в начало очереди");
        }

        $res = $this->getConnection()->queue()->delete([1, $queue_count]);

        $this->log->debug('MPD: Очищаю очередь');

        if (!$res) {
            throw new \RuntimeException("MPD: ошибка очистки очереди");
        }

        return $res;
    }

    private function getQueueCount(): int
    {
        /** @var array */
        $queued_songs = $this->getConnection()->queue()->search(new Filter('file', 'contains', '/'));

        if ($queued_songs == false) {
            return 0;
        }

        return sizeof($queued_songs);
    }

    private function getConnection(): MphpD
    {
        if (!$this->mphpd->connected) {
            $this->log->info('MPD: Выполняю подключение к серверу');

            try {
                $this->mphpd->connect();
            } catch (MPDException $e) {
                $this->log->error('MPD: произошла ошибка при подключении к серверу: ' . $e->getMessage(), ['error' => $this->mphpd->get_last_error()]);
            }
        }

        return $this->mphpd;
    }
}
