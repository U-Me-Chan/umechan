<?php

use Medoo\Medoo;
use Monolog\Handler\StreamHandler;
use Monolog\Level;
use Monolog\Logger;
use React\EventLoop\Loop;
use Ridouchire\RadioScheduler\Commercials;
use Ridouchire\RadioScheduler\Jingles;
use Ridouchire\RadioScheduler\Mpd;
use Ridouchire\RadioScheduler\QueueCropper;
use Ridouchire\RadioScheduler\RotationMaster;
use Ridouchire\RadioScheduler\RotationStrategies\ByEstimateInGenre;
use Ridouchire\RadioScheduler\RotationStrategies\RandomTracksInGenrePattern;
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

$random_tracks_in_genre_pattern = new RandomTracksInGenrePattern($db, $jingles, $commercials, $mpd, $log);
$by_estimate_in_genre_strategy  = new ByEstimateInGenre($db, $jingles, $commercials, $mpd, $log);

$strategy_master = new RotationMaster($log);

$strategy_master->addStrategy($by_estimate_in_genre_strategy);
$strategy_master->addStrategy($random_tracks_in_genre_pattern);

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
