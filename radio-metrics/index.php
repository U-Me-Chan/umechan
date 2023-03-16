<?php

require_once __DIR__ . '/vendor/autoload.php';

use Monolog\Level;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use React\EventLoop\Loop;
use Medoo\Medoo;
use Ridouchire\RadioMetrics\Collectors\IcecastCollector;
use Ridouchire\RadioMetrics\DTOs\CollectorData;
use Ridouchire\RadioMetrics\Storage\Repository;
use Ridouchire\RadioMetrics\Storage\Entites\Track;
use Ridouchire\RadioMetrics\Storage\Entites\Record;

$log = new Logger('log');
$log->pushHandler(new StreamHandler(__DIR__ . '/logs/radio-metrics.log', Level::Debug));

try {
    $db = new Medoo([
        'database_type' => 'mysql',
        'database_name' => isset($_ENV['MYSQL_DATABASE']) ? $_ENV['MYSQL_DATABASE'] : 'test',
        'server'        => isset($_ENV['MYSQL_HOSTNAME']) ? $_ENV['MYSQL_HOSTNAME'] : 'localhost',
        'username'      => isset($_ENV['MYSQL_USERNAME']) ? $_ENV['MYSQL_USERNAME'] : 'dev',
        'password'      => isset($_ENV['MYSQL_PASSWORD']) ? $_ENV['MYSQL_PASSWORD'] : 'dev',
        'charset'       => 'utf8',
        'collation'     => 'utf8_unicode_ci'
    ]);
} catch (\PDOException $e) {
    $log->error('Не могу подключиться к СУБД', [$e->getMessage()]);

    return;
}

$repo = new Repository($db);

try {
    $collector = new IcecastCollector(isset($_ENV['RADIO_API_URL']) ? $_ENV['RADIO_API_URL'] : 'http://192.168.88.175:9000/status-json.xsl');
} catch (\Throwable $e) {
    $log->error('Не могу запустить клиент доступа к источнику данных', [$e->getMessage()]);
}

$listeners = 0;
$last_track    = '';

Loop::addPeriodicTimer(1, function () use ($collector, $repo, $log, &$listeners, &$last_track) {
    $log->debug('Запрашиваем данные');

    try {
        /** @var CollectorData */
        $data = $collector->getData();
    } catch (RuntimeException $e) {
        $log->error('Произошла ошибка при запросе данных из источника', [$e->getMessage()]);

        return;
    }

    if (empty($last_track)) {
        $log->debug('Первый запуск, кеширую данные');
        $last_track = $data->getTrack();

        return;
    }

    try {
        /** @var Track */
        $track = $repo->findOne(Track::draft(), ['track' => $data->getTrack()]);
    } catch (\RuntimeException) {
        $track = Track::draft($data->getTrack());
    }

    if ($data->getListeners() !== 0) {
        $log->debug('Увеличиваю оценку', ['track' => $data->getTrack(), 'listeners' => $data->getListeners()]);
        $track->bumpEstimate($data->getListeners());
    }

    if ($data->getTrack() !== $last_track) {
        $log->debug('Трек изменился', ['track' => $data->getTrack(), 'old_track' => $track]);
        $track->togglePlaying();
        $track->bumpPlayCount();
    }

    $listeners  = $data->getListeners();
    $last_track = $data->getTrack();

    $track_id = $repo->save($track);

    $record = Record::draft($track_id, $listeners);
    $repo->save($record);

    $log->debug('Текущие данные', ['track' => $last_track, 'listeners' => $listeners]);
});
