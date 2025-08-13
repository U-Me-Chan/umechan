<?php

use Medoo\Medoo;
use Monolog\Handler\StreamHandler;
use Monolog\Level;
use Monolog\Logger;
use React\EventLoop\Loop;
use Ridouchire\RadioDbImporter\DirectoryIterator;
use Ridouchire\RadioDbImporter\FileManager;
use Ridouchire\RadioDbImporter\Handler;
use Ridouchire\RadioDbImporter\Id3v2Parser;
use Ridouchire\RadioDbImporter\Tracks\TrackRepository;
use Ridouchire\RadioDbImporter\Utils\PathCutter;

require_once __DIR__ . DIRECTORY_SEPARATOR . "vendor/autoload.php";
require_once __DIR__ . DIRECTORY_SEPARATOR . "vendor/james-heinrich/getid3/getid3/getid3.php";

$logger = new Logger('log');
$logger->pushHandler(new StreamHandler(__DIR__ . DIRECTORY_SEPARATOR . 'logs/radio-db-importer.log', Level::Info));
$logger->info('Запуск');

$music_dir_path                  = '/var/lib/music';
$music_dir_of_convertible_files  = '/var/lib/convert';
$music_dir_of_files_without_tags = '/var/lib/tagme';
$music_dir_of_negative_estimates = '/var/lib/music/Duplicate';

$db = new Medoo([
    'database_type' => 'mysql',
    'database_name' => $_ENV['MYSQL_DATABASE'],
    'server'        => $_ENV['MYSQL_HOSTNAME'],
    'username'      => $_ENV['MYSQL_USERNAME'],
    'password'      => $_ENV['MYSQL_PASSWORD'],
    'charset'       => 'utf8mb4',
    'collation'     => 'utf8mb4_unicode_ci'
]);

$path_cutter  = new PathCutter($music_dir_path);
$dir_iterator = new DirectoryIterator($music_dir_path);
$tags_parser  = new Id3v2Parser(new getID3());
$track_repo   = new TrackRepository($db);
$file_manager = new FileManager($music_dir_of_convertible_files, $music_dir_of_files_without_tags, $music_dir_of_negative_estimates);
$handler      = new Handler($dir_iterator, $tags_parser, $logger, $track_repo, $file_manager, $path_cutter);

Loop::addPeriodicTimer(0.01, $handler);
