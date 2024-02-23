<?php

use FloFaber\MphpD\MphpD;
use Monolog\Handler\StreamHandler;
use Monolog\Level;
use Monolog\Logger;
use React\EventLoop\Loop;
use Ridouchire\RadioScheduler\Mpd;
use Ridouchire\RadioScheduler\RotationMaster;
use Ridouchire\RadioScheduler\RotationStrategies\Weekday;

require_once __DIR__ . '/vendor/autoload.php';

$_ENV['MPD_HOSTNAME'] = '192.168.88.159';
$_ENV['MPD_PORT']     = 6600;

$log = new Logger('log');
$log->pushHandler(new StreamHandler(__DIR__ . '/logs/radio-scheduler.log', Level::Info));
$log->info('Запуск');

$mphpd = new MphpD([
    'host' => $_ENV['MPD_HOSTNAME'],
    'port' => $_ENV['MPD_PORT'],
    'timeout' => 5
]);

$mpd = new Mpd($log, $_ENV['MPD_HOSTNAME'], $_ENV['MPD_PORT']);

$weekday_strategy = new Weekday($mpd, $log);

$strategy_master = new RotationMaster($weekday_strategy);

Loop::addPeriodicTimer(1, function () use ($strategy_master) {
    $strategy_master->execute();
});
