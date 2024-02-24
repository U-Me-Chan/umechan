<?php

use Monolog\Handler\StreamHandler;
use Monolog\Level;
use Monolog\Logger;
use React\EventLoop\Loop;
use Ridouchire\RadioScheduler\Mpd;
use Ridouchire\RadioScheduler\RotationMaster;
use Ridouchire\RadioScheduler\RotationStrategies\Weekday;

require_once __DIR__ . '/vendor/autoload.php';

$log = new Logger('log');
$log->pushHandler(new StreamHandler(__DIR__ . '/logs/radio-scheduler.log', Level::Info));
$log->info('Запуск');

$mpd = new Mpd($log, $_ENV['MPD_HOSTNAME'], $_ENV['MPD_PORT']);

$weekday_strategy = new Weekday($mpd, $log);

$strategy_master = new RotationMaster($weekday_strategy);

Loop::addPeriodicTimer(1, function () use ($strategy_master, $log, $mpd) {
    try {
        $strategy_master->execute();
    } catch (\Throwable $e) {
        $log->error('MainLoop: произошла ошибка при исполнении стратегии ротации', [
            'error' => $e->getMessage(),
            'file'  => $e->getFile(),
            'line'  => $e->getLine()
        ]);
    }

    if ($mpd->isEmptyQueue()) {
        $log->error('MainLoop: очередь воспроизведения пуста');
    }
});
