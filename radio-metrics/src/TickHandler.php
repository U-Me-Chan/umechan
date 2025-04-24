<?php

namespace Ridouchire\RadioMetrics;

use RuntimeException;
use Monolog\Logger;
use Ridouchire\RadioMetrics\ICache;
use Ridouchire\RadioMetrics\Collectors\MpdCollector;
use Ridouchire\RadioMetrics\Collectors\IcecastCollector;
use Ridouchire\RadioMetrics\Exceptions\EntityNotFound;
use Ridouchire\RadioMetrics\Storage\Entites\Record;
use Ridouchire\RadioMetrics\Storage\Entites\Track;
use Ridouchire\RadioMetrics\Storage\RecordRepository;
use Ridouchire\RadioMetrics\Storage\TrackRepository;
use Ridouchire\RadioMetrics\Utils\Md5Hash;

class TickHandler
{
    public function __construct(
        private Logger $logger,
        private MpdCollector $mpdCollector,
        private IcecastCollector $icecastCollector,
        private TrackRepository $trackRepository,
        private RecordRepository $recordRepository,
        private Md5Hash $md5Hash,
        private ICache $cache
    ) {
    }

    public function handle(): void
    {
        $this->logger->debug('Начало цикла коллекционирования');
        $this->logger->debug('Запрашиваю данные о текущем треке у MPD');

        try {
            $filepath = $this->getFilePathFromMPD();
        } catch (RuntimeException $e) {
            $this->logger->error('Ошибка при запросе данных у MPD', [$e]);

            return;
        }

        $cached_track_data = $this->cache->get('current_track');
        $cached_estimate   = $this->cache->get('estimate') ?? 0;

        if ($cached_track_data == null || $cached_track_data['path'] !== $filepath) {
            $this->logger->debug('Пытаемся найти композицию в БД');

            try {
                /** @var Track */
                $track = $this->trackRepository->findOne([
                    'hash' => $this->md5Hash->get($filepath)
                ]);

                $this->logger->debug('Нашли композицию в БД по её md5-хешу');
            } catch (EntityNotFound) {
                $this->logger->error('Трек не найден: ' . $filepath);

                return;
            } catch (RuntimeException) {
                $this->logger->error('Не могу вычислить хеш-сумму для файла композиции: ' . $filepath);

                return;
            }

            $this->logger->debug('Трек изменился', ['track' => $track->toArray(), 'old_track' => $cached_track_data]);

            if ($cached_track_data !== null) {
                $_track = $this->trackRepository->findOne(['hash' => $cached_track_data['hash']]);
                $_track->setEstimate($cached_estimate);

                $this->trackRepository->save($_track);
            }

            $this->logger->debug('Увеличиваю счётчик проигрываний');
            $track->bumpPlayCount();

            $this->logger->debug('Изменяю дату последнего воспроизведения');
            $track->togglePlaying();

            $this->trackRepository->save($track);

            $this->logger->debug('Обновляю данные трека', ['track' => $track->toArray()]);

            $this->cache->set('current_track', $track->toArray());
            $this->cache->set('estimate', $track->getEstimate());
        } else {
            $track = Track::fromArray($cached_track_data);
        }

        try {
            $this->logger->debug('Запрашиваю данные о слушателях из Icecast');

            $listeners_data = $this->icecastCollector->getData();
            $listeners      = $listeners_data['listeners'];
        } catch (\RuntimeException $e) {
            $this->logger->error("Произошла ошибка при запросе данных из Icecast", [$e->getMessage()]);

            return;
        }

        if ($listeners !== 0) {
            $this->logger->debug("Увеличиваю оценку трека", ['track' => $track->getName(), 'listeners' => $listeners]);
            $this->cache->increment('estimate', $listeners);
        }

        $this->logger->debug('Сохраняю данные о слушателях трека');

        $record = Record::draft($track->getId(), $listeners);
        $this->recordRepository->save($record);

        $this->logger->debug('Текущие данные', ['track' => $track->toArray(), 'listeners' => $listeners]);
        $this->logger->debug('Конец цикла коллекционирования');
    }

    /**
     * @throws RuntimeException
     */
    public function getFilePathFromMPD(): string
    {
        return $this->mpdCollector->getData()['file'];
    }
}
