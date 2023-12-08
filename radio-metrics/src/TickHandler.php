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
use RuntimeException;

class TickHandler
{
    private ?Track $last_track;

    public function __construct(
        private Logger $logger,
        private MpdCollector $mpdCollector,
        private IcecastCollector $icecastCollector,
        private SenderProvider $senderProvider,
        private TrackRepository $trackRepository,
        private RecordRepository $recordRepository
    ) {
        $this->last_track = null;
    }

    public function __invoke(): void
    {
        $this->logger->debug('Начало цикла коллекционирования');

        try {
            $this->logger->debug('Запрашиваю данные о текущем треке у MPD');

            $track_data = $this->mpdCollector->getData();
        } catch (RuntimeException) {
            $this->logger->error("Не могу получить данные о треке из MPD");

            return;
        }

        try {
            $track = $this->trackRepository->findOne(['mpd_track_id' => $track_data['id']]);

            $this->logger->debug('Трек найден в БД', ['track' => $track]);
        } catch (EntityNotFound) {
            $track = Track::draft(
                $track_data['artist'],
                $track_data['title'],
                $track_data['time'],
                $track_data['file'],
                $track_data['id']
            );

            $this->logger->debug('Трек не найден в БД, будет сохранён новый', ['track' => $track]);
        }

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
        } catch (RuntimeException $e) {
            $this->logger->error("Произошла ошибка при запросе данных из Icecast", [$e->getMessage()]);

            return;
        }

        if ($listeners !== 0) {
            $this->logger->debug("Увеличиваю оценку трека", ['track' => $track->getName(), 'listeners' => $listeners]);
            $track->bumpEstimate($listeners);
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
}
