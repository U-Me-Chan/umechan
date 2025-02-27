<?php

require_once __DIR__ . '/vendor/autoload.php';

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use React\EventLoop\Loop;
use Ridouchire\RadioMetrics\Collectors\IcecastCollector;
use Ridouchire\RadioMetrics\Collectors\MpdCollector;
use Ridouchire\RadioMetrics\Http\Controllers\EstimateTrack;
use Ridouchire\RadioMetrics\SenderProvider;
use Ridouchire\RadioMetrics\Senders\DummySender;
use Ridouchire\RadioMetrics\Storage\DbConnector;
use Ridouchire\RadioMetrics\Storage\RecordRepository;
use Ridouchire\RadioMetrics\Storage\TrackRepository;
use Ridouchire\RadioMetrics\Services\Mpd;
use Ridouchire\RadioMetrics\TickHandler;
use Ridouchire\RadioMetrics\Http\Controllers\GetInfo;
use Ridouchire\RadioMetrics\Http\Router;
use Ridouchire\RadioMetrics\Utils\Container;
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
    //$senderProvider->attach(new DiscordWebHookSender($env->radio_discord_hook_url));
}

$icecastCollector = new IcecastCollector($env->radio_api_url);
$mpdCollector     = new MpdCollector($env->mpd_hostname, $env->mpd_port);

$trackRepo  = new TrackRepository($db);
$recordRepo = new RecordRepository($db);

$cache = new Container();

$tickHandler = new TickHandler($logger, $mpdCollector, $icecastCollector, $senderProvider, $trackRepo, $recordRepo, new Md5Hash($env->mpd_database_path), $cache);

Loop::addPeriodicTimer(1, $tickHandler);

$mpd = new Mpd($logger, $env->mpd_hostname, $env->mpd_port);

$r = new Router();
$r->addRoute('GET', '/metrics/info', new GetInfo($cache));
$r->addRoute('POST', '/metrics/tracks/{id}', new EstimateTrack($trackRepo, $cache));

$http = new React\Http\HttpServer($r);
$socket = new React\Socket\SocketServer('0.0.0.0:8080');
$http->listen($socket);
