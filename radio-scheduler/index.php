<?php

use Medoo\Medoo;
use Monolog\Handler\StreamHandler;
use Monolog\Level;
use Monolog\Logger;
use React\EventLoop\Loop;
use Ridouchire\RadioScheduler\Mpd;
use Ridouchire\RadioScheduler\Tasks\Services\SequenceGenerator;
use Ridouchire\RadioScheduler\Tasks\Services\TaskManager;
use Ridouchire\RadioScheduler\Tasks\Services\TaskParser;
use Ridouchire\RadioScheduler\Tasks\Services\TracklistGenerators\AverageEstimateTracklistGenerator;
use Ridouchire\RadioScheduler\Tasks\Services\TracklistGenerators\BestEstimateTracklistGenerator;
use Ridouchire\RadioScheduler\Tasks\Services\TracklistGenerators\NewOrLongStandingTracklistGenerator;
use Ridouchire\RadioScheduler\Tasks\Services\TracklistGenerators\RandomTracklistGenerator;
use Ridouchire\RadioScheduler\Tasks\Services\YamlSchemasDirectoryIterator;
use Ridouchire\RadioScheduler\Tasks\Task;

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

$random_tracklist_generator               = new RandomTracklistGenerator($db);
$new_or_long_standing_tracklist_generator = new NewOrLongStandingTracklistGenerator($db);
$average_estimate_tracklist_generator     = new AverageEstimateTracklistGenerator($db);
$best_estimate_tracklist_generator        = new BestEstimateTracklistGenerator($db);

$sequence_generator = new SequenceGenerator(
    $average_estimate_tracklist_generator,
    $best_estimate_tracklist_generator,
    $new_or_long_standing_tracklist_generator,
    $random_tracklist_generator
);

$task_manager = new TaskManager($sequence_generator, $mpd, $log);
$task_parser  = new TaskParser();
$iterator     = new YamlSchemasDirectoryIterator(__DIR__ . '/schemas/');

foreach ($iterator->getIterator() as $yaml_file) {
    $tasks = $task_parser->parseFromFile($yaml_file);

    array_walk($tasks, function (Task $task) use ($task_manager) {
        $task_manager->add($task);
    });
}

Loop::addPeriodicTimer(1, function () use ($log, $mpd, $task_manager) {
    if ($mpd->isEmptyQueue()) {
        $log->error('MainLoop: очередь воспроизведения пуста');
    }

    if ($mpd->getQueueCount() <= 1) {
        $res = $task_manager->tick();

        if ($res == TaskManager::EXIT_CODE_ERR_NO_TASKS) {
            $log->error('MainLoop: нет задач в менеджере');
        } else if ($res == TaskManager::EXIT_CODE_ERR_NO_TASK_ON_TIME) {
            $log->error('MainLoop: нет ни одной задачи на это время');
        }
    }

    try {
        if ($task_manager->cropQueue(time()) == false) {
            $log->error('MainLoop: Ошибка при очищении очереди воспроизведения');
        }
    } catch (Exception) {
        $log->debug('MainLoop: Время очищения очереди воспроизведения ещё не пришло');
    }
});
