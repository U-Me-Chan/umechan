<?php

namespace Ridouchire\RadioMetrics;

use Monolog\Logger;
use Ridouchire\RadioMetrics\SenderProvider;
use Ridouchire\RadioMetrics\Collectors\MpdCollector;
use Ridouchire\RadioMetrics\Collectors\IcecastCollector;
use Ridouchire\RadioMetrics\Exceptions\EntityNotFound;
use Ridouchire\RadioMetrics\Storage\Entites\Record;
use Ridouchire\RadioMetrics\Storage\Entites\Track;
use Ridouchire\RadioMetrics\Storage\RecordRepository;
use Ridouchire\RadioMetrics\Storage\TrackRepository;
use Ridouchire\RadioMetrics\Utils\Container;
use Ridouchire\RadioMetrics\Utils\Md5Hash;

class TickHandler
{
    private ?Track $last_track;

    public function __construct(
        private Logger $logger,
        private MpdCollector $mpdCollector,
        private IcecastCollector $icecastCollector,
        private SenderProvider $senderProvider,
        private TrackRepository $trackRepository,
        private RecordRepository $recordRepository,
        private Md5Hash $md5Hash,
        private Container $cache
    ) {
        $this->last_track = null;
    }

    public function __invoke(): void
    {
        $this->logger->debug('Начало цикла коллекционирования');

        try {
            $this->logger->debug('Запрашиваю данные о текущем треке у MPD');

            $track_data = $this->mpdCollector->getData();
        } catch (\RuntimeException $e) {
            $this->logger->error("Не могу получить данные о треке из MPD", [$e->getMessage()]);

            return;
        }

        /** @var Track */
        $track = $this->getTrack($track_data);

        $this->cache->current_track = $track->toArray();

        if ($this->last_track == null) {
            $this->logger->debug('Кеширую данные');

            $this->last_track = $track;

            $track->bumpPlayCount();
            $track->togglePlaying();
        }

        try {
            $this->logger->debug('Запрашиваю данные о слушателях из Icecast');

            $listeners_data = $this->icecastCollector->getData();
            $listeners = $listeners_data['listeners'];
        } catch (\RuntimeException $e) {
            $this->logger->error("Произошла ошибка при запросе данных из Icecast", [$e->getMessage()]);

            return;
        }

        if ($listeners !== 0) {
            $this->logger->debug("Увеличиваю оценку трека", ['track' => $track->getName(), 'listeners' => $listeners]);
            $track->increaseEstimate($listeners);
        }

        if ($track->getName() !== $this->last_track->getName()) {
            $this->logger->debug('Трек изменился', ['track' => $track->getName(), 'old_track' => $this->last_track]);
            $track->bumpPlayCount();
            $track->togglePlaying();

            $this->logger->debug('Передаю данные о треке и слушателях отправителям оповещений');
            $this->senderProvider->send($track, $listeners);
        }

        $this->last_track = $track;

        $track_id = $this->trackRepository->save($track);

        $this->logger->debug('Обновляю данные трека', ['track_id' => $track_id]);

        $record = Record::draft($track_id, $listeners);

        $this->recordRepository->save($record);

        $this->logger->debug('Сохраняю данные о слушателях трека');

        $this->logger->debug('Текущие данные', ['track' => $this->last_track, 'listeners' => $listeners]);

        $this->logger->debug('Конец цикла коллекционирования');
    }

    private function getTrack(array $mpd_track_data): Track
    {
        $this->logger->debug('Пытаемся найти композицию в БД');

        try {
             $track = $this->trackRepository->findOne([
                 'hash' => $this->md5Hash->get($mpd_track_data['file'])
             ]);

             $this->logger->debug('Нашли композицию в БД по её md5-хешу');

             return $track;
        } catch (EntityNotFound) {
            $this->logger->debug('Композиция не найдена в БД, будет создан новый');

            return Track::draft(
                $mpd_track_data['artist'],
                $mpd_track_data['title'],
                $this->md5Hash->get($mpd_track_data['file']),
                $mpd_track_data['file'],
                $mpd_track_data['time'],
                $mpd_track_data['id'],
            );
        }
    }
}
