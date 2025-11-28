<?php

use Medoo\Medoo;
use Monolog\Handler\StreamHandler;
use Monolog\Level;
use Monolog\Logger;
use React\EventLoop\Loop;
use Ridouchire\RadioScheduler\Http\Controllers\CropQueue;
use Ridouchire\RadioScheduler\Http\Controllers\GetOpenApiSpecifitation;
use Ridouchire\RadioScheduler\Http\Controllers\GetQueue;
use Ridouchire\RadioScheduler\Http\Controllers\GetRedocPage;
use Ridouchire\RadioScheduler\Http\Controllers\GetTrackList;
use Ridouchire\RadioScheduler\Http\Controllers\OrderTrack;
use Ridouchire\RadioScheduler\Http\Controllers\OrderTracklist;
use Ridouchire\RadioScheduler\Http\Router;
use Ridouchire\RadioScheduler\Services\Mpd;
use Ridouchire\RadioScheduler\Services\QueueCropper;
use Ridouchire\RadioScheduler\RotationMaster;
use Ridouchire\RadioScheduler\RotationStrategies\DayGenreRotation;
use Ridouchire\RadioScheduler\RotationStrategies\DayRandomPatternRotation;
use Ridouchire\RadioScheduler\RotationStrategies\EveningGenreRotation;
use Ridouchire\RadioScheduler\RotationStrategies\MorningRotationStrategy;
use Ridouchire\RadioScheduler\RotationStrategies\NightGenreRotation;
use Ridouchire\RadioScheduler\RotationStrategies\OddFridayRotation;
use Ridouchire\RadioScheduler\RotationStrategies\OddMiddayFridayRotation;
use Ridouchire\RadioScheduler\RotationStrategies\SimpleMiddayFridayRotation;
use Ridouchire\RadioScheduler\Services\OrderTrackService;
use Ridouchire\RadioScheduler\Services\RandomizerFromRandomPackageWrapper;
use Ridouchire\RadioScheduler\TracklistGenerators\AverageEstimateTracklistGenerator;
use Ridouchire\RadioScheduler\TracklistGenerators\BestEstimateTracklistGenerator;
use Ridouchire\RadioScheduler\TracklistGenerators\NewOrLongStandingTracklistGenerator;
use Ridouchire\RadioScheduler\TracklistGenerators\RandomTracklistGenerator;

require_once __DIR__ . '/vendor/autoload.php';

date_default_timezone_set('Europe/Ulyanovsk');

$log = new Logger('scheduler');
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

$randomizer = new RandomizerFromRandomPackageWrapper();

$random_tracklist_generator               = new RandomTracklistGenerator($db);
$new_or_long_standing_tracklist_generator = new NewOrLongStandingTracklistGenerator($db, $randomizer);
$average_estimate_tracklist_generator     = new AverageEstimateTracklistGenerator($db, $randomizer);
$best_estimate_tracklist_generator        = new BestEstimateTracklistGenerator($db);

$strategy_master = new RotationMaster($log);

$strategy_master->addStrategyByPeriod(0, 5, new NightGenreRotation($mpd, $log, $new_or_long_standing_tracklist_generator, $average_estimate_tracklist_generator));
$strategy_master->addStrategyByPeriod(6, 8, new MorningRotationStrategy($mpd, $log, $random_tracklist_generator));
$strategy_master->addStrategyByPeriod(12, 15, new OddMiddayFridayRotation($mpd, $log, $new_or_long_standing_tracklist_generator, $average_estimate_tracklist_generator, $random_tracklist_generator));
$strategy_master->addStrategyByPeriod(12, 15, new SimpleMiddayFridayRotation($mpd, $log, $new_or_long_standing_tracklist_generator, $average_estimate_tracklist_generator));
$strategy_master->addStrategyByPeriod(9, 19, new DayGenreRotation($mpd, $log, $new_or_long_standing_tracklist_generator, $average_estimate_tracklist_generator));
$strategy_master->addStrategyByPeriod(9, 19, new DayRandomPatternRotation($mpd, $log, $random_tracklist_generator));
$strategy_master->addStrategyByPeriod(19, 23, new EveningGenreRotation($mpd, $log, $new_or_long_standing_tracklist_generator, $average_estimate_tracklist_generator));

$queue_cropper = new QueueCropper($mpd);

Loop::addPeriodicTimer(1, function () use ($log, $mpd, $queue_cropper, $strategy_master) {
    if ($mpd->isEmptyQueue()) {
        $log->error('MainLoop: очередь воспроизведения пуста');
    }

    if ($mpd->getQueueCount() <= 1) {
        $strategy_master->execute();
    }

    try {
        if ($queue_cropper(time()) == false) {
            $log->error('QueueCropper: Ошибка при очищении очереди воспроизведения');
        }
    } catch (Exception) {
        $log->debug('QueueCropper: Время очищения очереди воспроизведения ещё не пришло');
    }
});

$order_track_service = new OrderTrackService($mpd, $db, $log, $random_tracklist_generator);

$r = new Router();

$r->addRoute('GET', '/radio/docs/openapi.json', new GetOpenApiSpecifitation());
$r->addRoute('GET', '/radio/docs/redoc.html', new GetRedocPage());
$r->addRoute('GET', '/radio/queue', new GetQueue($mpd));
$r->addRoute('PUT', '/radio/queue', new OrderTrack($order_track_service));
$r->addRoute('POST', '/radio/queue', new OrderTracklist($order_track_service));
$r->addRoute('DELETE', '/radio/queue', new CropQueue($queue_cropper));
$r->addRoute('GET', '/radio/tracks', new GetTrackList($db));

$http = new React\Http\HttpServer($r);
$socket = new React\Socket\SocketServer('0.0.0.0:8080');
$http->listen($socket);
