<?php

require_once __DIR__ . '/vendor/autoload.php';

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use React\EventLoop\Loop;
use Ridouchire\RadioMetrics\Cache\Memcached;
use Ridouchire\RadioMetrics\Collectors\IcecastCollector;
use Ridouchire\RadioMetrics\Collectors\MpdCollector;
use Ridouchire\RadioMetrics\Http\Controllers\EstimateTrack;
use Ridouchire\RadioMetrics\Storage\DbConnector;
use Ridouchire\RadioMetrics\Storage\RecordRepository;
use Ridouchire\RadioMetrics\Storage\TrackRepository;
use Ridouchire\RadioMetrics\TickHandler;
use Ridouchire\RadioMetrics\Http\Controllers\GetInfo;
use Ridouchire\RadioMetrics\Http\Router;
use Ridouchire\RadioMetrics\Utils\Environment;
use Ridouchire\RadioMetrics\Utils\Md5Hash;

if (file_exists(__DIR__ . '/local.env.php')) {
    require_once __DIR__ . '/local.env.php';
}

$env = new Environment($_ENV);

$logger = new Logger('metrics');
$logger->pushHandler(new StreamHandler(__DIR__ . '/logs/radio-metrics.log', $env->radio_log_level));
$logger->info('Запуск');

try {
    $db = DbConnector::getConnection(
        $env->mysql_database,
        $env->mysql_hostname,
        $env->mysql_username,
        $env->mysql_password
    );
} catch (\PDOException $e) {
    $logger->error('Не могу подключиться к СУБД', [$e->getMessage()]);

    return;
}

$icecastCollector = new IcecastCollector(new GuzzleHttp\Client(['base_uri' => $env->radio_api_url]));
$mpdCollector     = new MpdCollector($env->mpd_hostname, $env->mpd_port);

$trackRepo  = new TrackRepository($db);
$recordRepo = new RecordRepository($db);

$cache = new Memcached('memcached', 11211);

$tickHandler = new TickHandler($logger, $mpdCollector, $icecastCollector, $trackRepo, new Md5Hash($env->mpd_database_path), $cache);

Loop::addPeriodicTimer(1, function () use ($tickHandler) {
    $tickHandler->handle();
}); // FIXME: я лох

$r = new Router();
$r->addRoute('GET', '/metrics/info', new GetInfo($cache));
$r->addRoute('POST', '/metrics/tracks/{id:[0-9]+}', new EstimateTrack($trackRepo, $cache));

$http = new React\Http\HttpServer($r);
$socket = new React\Socket\SocketServer('0.0.0.0:8080');
$http->listen($socket);
