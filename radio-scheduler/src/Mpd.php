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
     * Возвращает список треков
     *
     * @param string $dir   Относительный путь директории
     * @param int    $start Начальный индекс
     * @param int    $end   Конечный индекс
     *
     * @throws \RuntimeException
     *
     * @return array
     */
    public function getTracks(string $dir, int $start, int $end): array
    {
        /** @var array|false */
        $track_datas = $this->mphpd->db()->search(new Filter('file', 'contains', "{$dir}/"), '', [$start, $end]);

        if ($track_datas === false) {
            throw new \RuntimeException("MPD: нет такого трека: {$dir}:{$start}:{$end}");
        }

        return $track_datas;
    }

    /**
     * Возвращает количество композиций в директории
     *
     * @param string $dir Относительный путь
     *
     * @throws RuntimeException
     *
     * @return int
     */
    public function getCountSongsInDirectory(string $pls): int
    {
        /** @var array|false */
        $pls_data = $this->getConnection()->db()->count(new Filter('file', 'contains', "{$pls}/"));

        if (!$pls_data) {
            throw new \RuntimeException("MPD: ошибка при попытке подсчёта количества композиций в {$pls}");
        }

        return $pls_data['songs'];
    }

    /**
     * Пуста ли очередь?
     *
     * @return bool
     */
    public function isEmptyQueue(): bool
    {
        return $this->getQueueCount() == 0 ? true : false;
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

    /**
     * Возвращает количество композиций в очереди
     *
     * @return int
     */
    public function getQueueCount(): int
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
