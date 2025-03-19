<?php

use Medoo\Medoo;
use Rweb\App;
use Rweb\Middlewares\Router;
use Symfony\Component\HttpFoundation\Request;
use IH\Controllers\UploadFile;
use IH\Controllers\GetFilelist;
use IH\Controllers\GetFile;
use IH\Controllers\DeleteFile;
use IH\Services\TelegramSender;
use IH\Services\ThumbnailCreator;
use IH\Services\Thumbnailers\ImageThumbnailer;
use IH\Services\Thumbnailers\VideoThumbnailer;

require "vendor/autoload.php";

$app = App::create();

$db = new Medoo([
    'database_type' => 'mysql',
    'database_name' => $_ENV['MYSQL_DATABASE'],
    'server'        => $_ENV['MYSQL_HOSTNAME'],
    'username'      => $_ENV['MYSQL_USERNAME'],
    'password'      => $_ENV['MYSQL_PASSWORD'],
    'charset'       => 'utf8mb4',
    'collation'     => 'utf8mb4_unicode_ci'
]);

$thumbnail_creator = new ThumbnailCreator();
$thumbnail_creator->register(new ImageThumbnailer(__DIR__ . '/files/'));
$thumbnail_creator->register(new VideoThumbnailer(__DIR__ . '/files/'));

$telegram_sender = new TelegramSender($_ENV['FILESTORE_TELEGRAM_BOT_TOKEN'], $_ENV['FILESTORE_TELEGRAM_CHAT_ID']);

/** @var Router */
$r = new Router();

$r->addRoute('GET', '/filestore/files', new GetFilelist($_ENV['STATIC_URL'], $_ENV['ADMINISTRATOR_KEY']));
$r->addRoute('GET', '/filestore/files/{id:[0-9a-z\.]+}', new GetFile($_ENV['STATIC_URL'], $db));
$r->addRoute('POST', '/filestore', new UploadFile($_ENV['STATIC_URL'], $thumbnail_creator, $telegram_sender));
$r->addRoute('DELETE', '/filestore/files/{id:[0-9a-z\.]+}', new DeleteFile($_ENV['ADMINISTRATOR_KEY']));

$app->addMiddleware($r);

$app->run(Request::createFromGlobals());
