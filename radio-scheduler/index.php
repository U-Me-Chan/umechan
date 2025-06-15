<?php

use Medoo\Medoo;
use Monolog\Handler\StreamHandler;
use Monolog\Level;
use Monolog\Logger;
use React\EventLoop\Loop;
use Ridouchire\RadioScheduler\Commercials;
use Ridouchire\RadioScheduler\Http\Controllers\GetOpenApiSpecifitation;
use Ridouchire\RadioScheduler\Http\Controllers\GetQueue;
use Ridouchire\RadioScheduler\Http\Controllers\GetRedocPage;
use Ridouchire\RadioScheduler\Http\Controllers\GetTrackList;
use Ridouchire\RadioScheduler\Http\Controllers\OrderTrack;
use Ridouchire\RadioScheduler\Http\Router;
use Ridouchire\RadioScheduler\Jingles;
use Ridouchire\RadioScheduler\Mpd;
use Ridouchire\RadioScheduler\QueueCropper;
use Ridouchire\RadioScheduler\RotationMaster;
use Ridouchire\RadioScheduler\RotationStrategies\ByEstimateInGenre;
use Ridouchire\RadioScheduler\RotationStrategies\GenrePattern;
use Ridouchire\RadioScheduler\RotationStrategies\HolydayRu\GenrePattern as HolydayRuGenrePattern;
use Ridouchire\RadioScheduler\TickHandler;
use Ridouchire\RadioScheduler\Utils\TickCounter;

require_once __DIR__ . '/vendor/autoload.php';

if (file_exists(__DIR__ . '/config.local.php')) {
    require_once __DIR__ . '/config.local.php';
}

$log = new Logger('log');
$log->pushHandler(new StreamHandler(__DIR__ . '/logs/radio-scheduler.log', Level::Info));
$log->info('Запуск');

$mpd = new Mpd($log, $_ENV['MPD_HOSTNAME'], $_ENV['MPD_PORT']);

$db = new Medoo([
    'database_type' => 'mysql',
    'database_name' => $_ENV['MYSQL_DATABASE'],
    'server'        => $_ENV['MYSQL_HOSTNAME'],
    'username'      => $_ENV['MYSQL_USERNAME'],
    'password'      => $_ENV['MYSQL_PASSWORD'],
    'charset'       => 'utf8mb4',
    'collation'     => 'utf8mb4_unicode_ci'
]);

$jingles     = new Jingles($db);
$commercials = new Commercials($db);

$genre_pattern_strategy        = new GenrePattern($db, $mpd, $jingles, $log);
$by_estimate_in_genre_strategy = new ByEstimateInGenre($db, $jingles, $commercials, $mpd, $log);

$strategy_master = new RotationMaster($log);

$strategy_master->addStrategy($genre_pattern_strategy);
$strategy_master->addStrategy($by_estimate_in_genre_strategy);

$tickHanlder   = new TickHandler($strategy_master);
$queue_cropper = new QueueCropper($mpd);

TickCounter::create(0);

Loop::addPeriodicTimer(1, function () use ($tickHanlder, $log, $mpd, $queue_cropper) {
    $tickHanlder();

    TickCounter::tick();

    if ($mpd->isEmptyQueue()) {
        $log->error('MainLoop: очередь воспроизведения пуста');
    }

    try {
        if ($queue_cropper(time() + (60 * 60 * 4)) == false) {
            $log->error('Ошибка при очищении очереди воспроизведения');
        }
    } catch (Exception) {
        $log->debug('Время очищения очереди воспроизведения ещё не пришло');
    }
});

$r = new Router();

$r->addRoute('GET', '/radio/docs/openapi.json', new GetOpenApiSpecifitation());
$r->addRoute('GET', '/radio/docs/redoc.html', new GetRedocPage());
$r->addRoute('GET', '/radio/queue', new GetQueue($mpd));
$r->addRoute('PUT', '/radio/queue', new OrderTrack($mpd, $db, $log));
$r->addRoute('GET', '/radio/tracks', new GetTrackList($db));

$http = new React\Http\HttpServer($r);
$socket = new React\Socket\SocketServer('0.0.0.0:8080');
$http->listen($socket);
