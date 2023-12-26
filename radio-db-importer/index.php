<?php

use FloFaber\MphpD\Filter;
use FloFaber\MphpD\MphpD;
use Medoo\Medoo;
use Monolog\Handler\StreamHandler;
use Monolog\Level;
use Monolog\Logger;
use React\EventLoop\Loop;

require_once "vendor/autoload.php";

$_ENV['MPD_HOSTNAME'] = '192.168.88.168';
$_ENV['MPD_PORT'] = 6600;

$logger = new Logger('log');
$logger->pushHandler(new StreamHandler(__DIR__ . '/logs/radio-db-importer.log', Level::Info));
$logger->info('Запуск');

$mphpd = new MphpD([
    'host' => $_ENV['MPD_HOSTNAME'],
    'port' => $_ENV['MPD_PORT'],
    'timeout' => 5
]);

$db = new Medoo([
    'database_type' => 'mysql',
    'database_name' => $_ENV['MYSQL_DATABASE'],
    'server'        => $_ENV['MYSQL_HOSTNAME'],
    'username'      => $_ENV['MYSQL_USERNAME'],
    'password'      => $_ENV['MYSQL_PASSWORD'],
    'charset'       => 'utf8mb4',
    'collation'     => 'utf8mb4_unicode_ci'
]);

$mpd_database_path = $_ENV['MPD_DATABASE_PATH'];

$num = 0;

Loop::addPeriodicTimer(1, function () use ($mphpd, $db, $logger, $mpd_database_path, &$num) {
    $logger->debug('Запуск цикла');

    if (!$mphpd->connected) {
        try {
            $logger->debug('Подключаюсь к MPD');

            $mphpd->connect();
        } catch (\FloFaber\MPDException) {
            $logger->error('Произошла ошибка подключения к MPD');

            return;
        }
    }

    /** @var array|false */
    $files = $mphpd->db()->search(new Filter('file', 'contains', '/'), '-Last-Modified', [$num, $num + 1]);

    if (!$files) {
        $num = 0;

        $logger->debug('Список пуст, начинаю сначала');
    }

    foreach ($files as $file) {
        if (!file_exists($mpd_database_path . '/' . $file['file'])) {
            $logger->error('Файл не существует: ' . $file['file']);

            continue;
        }

        $hash = md5_file($mpd_database_path . '/' . $file['file']);

        $track_data = $db->get('tracks', '*', ['hash' => $hash]);

        if ($track_data !== false) {
            $logger->debug('Файл уже добавлен: ' . $file['file']);

            if (empty($track_data['file'])) {
                $logger->info('Файл без пути, обновляю для ' . $file['file']);

                $db->update('tracks', [
                    'path' => $file['file']
                ], [
                    'hash' => $hash
                ]);

            }

            continue;
        }

        $logger->info('Добавляю файл: ' . $file['file']);

        $db->insert('tracks', [
            'artist'        => $file['artist'],
            'title'         => $file['title'],
            'duration'      => $file['time'],
            'hash'          => $hash,
            'estimate'      => 0,
            'first_playing' => time(),
            'last_playing'  => time(),
            'play_count'    => 0,
            'path'          => $file['file']
        ]);
    }

    $num++;

    $logger->debug('Конец цикла');
});
