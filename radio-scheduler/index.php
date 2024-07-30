<?php

use Medoo\Medoo;
use Monolog\Handler\StreamHandler;
use Monolog\Level;
use Monolog\Logger;
use React\EventLoop\Loop;
use Ridouchire\RadioScheduler\Mpd;
use Ridouchire\RadioScheduler\QueueCropper;
use Ridouchire\RadioScheduler\RotationMaster;
use Ridouchire\RadioScheduler\RotationStrategies\AverageInGenre;
use Ridouchire\RadioScheduler\RotationStrategies\ByEstimateInGenre;
use Ridouchire\RadioScheduler\RotationStrategies\GenrePattern;
use Ridouchire\RadioScheduler\RotationStrategies\JingleAndCommercialPattern;
use Ridouchire\RadioScheduler\RotationStrategies\NewInGenre;
use Ridouchire\RadioScheduler\RotationStrategies\TopInGenre;
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

$genre_pattern_strategy                 = new GenrePattern($mpd, $log);
$by_estimate_in_genre_strategy          = new ByEstimateInGenre($db, $mpd, $log);
$jingle_and_commercial_pattern_strategy = new JingleAndCommercialPattern($db, $mpd, $log);

$strategy_master = new RotationMaster($log);

$strategy_master->addStrategy($genre_pattern_strategy);
$strategy_master->addStrategy($by_estimate_in_genre_strategy);

$tickHanlder = new TickHandler($strategy_master);
$queue_cropper = new QueueCropper($mpd);

TickCounter::create(0);

Loop::addPeriodicTimer(1, function () use ($tickHanlder, $log, $mpd, $queue_cropper, $jingle_and_commercial_pattern_strategy) {
    $tickHanlder();

    TickCounter::tick();

    if ($mpd->isEmptyQueue()) {
        $log->error('MainLoop: очередь воспроизведения пуста');
    }

    try {
        if ($queue_cropper(time() + (60 * 60 * 4)) == false) {
            $log->error('Ошибка при очищении очереди воспроизведения');
        }

        $jingle_and_commercial_pattern_strategy->execute();
    } catch (Exception) {
        $log->debug('Время очищения очереди воспроизведения ещё не пришло');
    }
});
