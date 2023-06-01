<?php

require_once __DIR__ . '/vendor/autoload.php';

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use React\EventLoop\Loop;
use Medoo\Medoo;
use Ridouchire\RadioMetrics\Collectors\IcecastCollector;
use Ridouchire\RadioMetrics\SenderProvider;
use Ridouchire\RadioMetrics\Senders\ChanSender;
use Ridouchire\RadioMetrics\Senders\DiscordWebHookSender;
use Ridouchire\RadioMetrics\DTOs\CollectorData;
use Ridouchire\RadioMetrics\Storage\Repository;
use Ridouchire\RadioMetrics\Storage\RepositoryStub;
use Ridouchire\RadioMetrics\Storage\Entites\Track;
use Ridouchire\RadioMetrics\Storage\Entites\Record;

if (file_exists(__DIR__ . '/local.env.php')) {
    require_once __DIR__ . '/local.env.php';
}

$log = new Logger('log');
$log->pushHandler(new StreamHandler(__DIR__ . '/logs/radio-metrics.log', $_ENV['RADIO_LOG_LEVEL']));

if (isset($_ENV['DB_STUB']) && $_ENV['DB_STUB'] == true) {
    $repo = new RepositoryStub();
} else {
    try {
        $db = new Medoo([
            'database_type' => 'mysql',
            'database_name' => $_ENV['MYSQL_DATABASE'],
            'server'        => $_ENV['MYSQL_HOSTNAME'],
            'username'      => $_ENV['MYSQL_USERNAME'],
            'password'      => $_ENV['MYSQL_PASSWORD'],
            'charset'       => 'utf8',
            'collation'     => 'utf8_unicode_ci'
        ]);
    } catch (\PDOException $e) {
        $log->error('Не могу подключиться к СУБД', [$e->getMessage()]);

        return;
    }

    $repo = new Repository($db);
}

$sender = new SenderProvider($log);
#$sender->attach(new ChanSender($_ENV['RADIO_CHAN_API_URL'], $_ENV['RADIO_CHAN_THREAD_ID'], $_ENV['RADIO_CHAN_POSTER_KEY']));
$sender->attach(new DiscordWebHookSender($_ENV['RADIO_DISCORD_HOOK_URL']));

try {
    $collector = new IcecastCollector($_ENV['RADIO_API_URL']);
} catch (\Throwable $e) {
    $log->error('Не могу запустить клиент доступа к источнику данных', [$e->getMessage()]);
}

$listeners = 0;
$last_track    = '';

Loop::addPeriodicTimer(1, function () use ($collector, $repo, $log, $sender, &$listeners, &$last_track) {
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

        $log->debug('Отправляю данные на внешние сервисы');
        $sender->send($track, $data->getListeners());
    }

    $listeners  = $data->getListeners();
    $last_track = $data->getTrack();

    $track_id = $repo->save($track);

    $record = Record::draft($track_id, $listeners);
    $repo->save($record);

    $log->debug('Текущие данные', ['track' => $last_track, 'listeners' => $listeners]);
});
