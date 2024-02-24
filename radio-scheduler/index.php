<?php

use Monolog\Handler\StreamHandler;
use Monolog\Level;
use Monolog\Logger;
use React\EventLoop\Loop;
use Ridouchire\RadioScheduler\Mpd;
use Ridouchire\RadioScheduler\RotationMaster;
use Ridouchire\RadioScheduler\RotationStrategies\GenrePattern;
use Ridouchire\RadioScheduler\RotationStrategies\Weekday;

require_once __DIR__ . '/vendor/autoload.php';

if (file_exists(__DIR__ . '/config.local.php')) {
    require_once __DIR__ . '/config.local.php';
}

$log = new Logger('log');
$log->pushHandler(new StreamHandler(__DIR__ . '/logs/radio-scheduler.log', Level::Info));
$log->info('Запуск');

$mpd = new Mpd($log, $_ENV['MPD_HOSTNAME'], $_ENV['MPD_PORT']);

$weekday_strategy       = new Weekday($mpd, $log);
$genre_pattern_strategy = new GenrePattern($mpd, $log);

$strategy_master = new RotationMaster();
$strategy_master->addStrategy($weekday_strategy);
$strategy_master->addStrategy($genre_pattern_strategy);

$current_strategy = Weekday::NAME;

Loop::addPeriodicTimer(1, function () use (&$current_strategy, $strategy_master, $log, $mpd) {
    /** @var int */
    $day_week = date('w', time() + (60 * 60 * 4));

    if (($day_week == 0 || $day_week == 6) &&
        $current_strategy == Weekday::NAME
    ) {
        $mpd->cropQueue();

        $log->info('MainLoop: текущая стратегия ротации: ' . GenrePattern::NAME);

        $current_strategy = GenrePattern::NAME;
    } else if ((in_array($day_week, range(1, 5))) &&
               $current_strategy == GenrePattern::NAME
    ) {
        $mpd->cropQueue();

        $log->info('MainLoop: текущая стратегия ротации: ' . Weekday::NAME);

        $current_strategy = Weekday::NAME;
    }

    try {
        $strategy_master->execute($current_strategy);
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
