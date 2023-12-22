<?php

require_once __DIR__ . '/vendor/autoload.php';

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use React\EventLoop\Loop;
use Ridouchire\RadioMetrics\Collectors\IcecastCollector;
use Ridouchire\RadioMetrics\Collectors\MpdCollector;
use Ridouchire\RadioMetrics\SenderProvider;
use Ridouchire\RadioMetrics\Senders\DiscordWebHookSender;
use Ridouchire\RadioMetrics\Senders\DummySender;
use Ridouchire\RadioMetrics\Storage\DbConnector;
use Ridouchire\RadioMetrics\Storage\RecordRepository;
use Ridouchire\RadioMetrics\Storage\TrackRepository;
use Ridouchire\RadioMetrics\TickHandler;
use Ridouchire\RadioMetrics\Utils\Environment;
use Ridouchire\RadioMetrics\Utils\Md5Hash;

if (file_exists(__DIR__ . '/local.env.php')) {
    require_once __DIR__ . '/local.env.php';
}

$env = new Environment($_ENV);

$logger = new Logger('log');
$logger->pushHandler(new StreamHandler(__DIR__ . '/logs/radio-metrics.log', $env->radio_log_level));
$logger->info('Запуск');


if ($env->is_dev == true) {
    $logger->debug('Включён режим разработки');
    $logger->debug('Выполняю миграции');

    exec('./vendor/bin/phinx migrate -e development');
}

try {
    if ($env->is_dev == true) {
        $logger->debug('Будет использован тестовая БД SQLite в файле');

        $db = DbConnector::getTestConnection();
    } else {
        $db = DbConnector::getConnection(
            $env->mysql_database,
            $env->mysql_hostname,
            $env->mysql_username,
            $env->mysql_password
        );
    }
} catch (\PDOException $e) {
    $logger->error('Не могу подключиться к СУБД', [$e->getMessage()]);

    return;
}

$senderProvider = new SenderProvider($logger);

if ($env->is_dev == true) {
    $senderProvider->attach(new DummySender());
} else {
    $senderProvider->attach(new DiscordWebHookSender($env->radio_discord_hook_url));
}

$icecastCollector = new IcecastCollector($env->radio_api_url);
$mpdCollector     = new MpdCollector($env->mpd_hostname, $env->mpd_port);

$trackRepo  = new TrackRepository($db);
$recordRepo = new RecordRepository($db);

$tickHandler = new TickHandler($logger, $mpdCollector, $icecastCollector, $senderProvider, $trackRepo, $recordRepo, new Md5Hash($env->mpd_database_path));

Loop::addPeriodicTimer(1, $tickHandler);
