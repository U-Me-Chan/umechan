<?php

use Medoo\Medoo;
use Rweb\App;
use Rweb\Middlewares\Router;
use Symfony\Component\HttpFoundation\Request;
use IH\Controllers\Index;
use IH\Controllers\UploadFile;
use IH\Controllers\GetFilelist;
use IH\Controllers\GetFile;

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

/** @var Router */
$r = new Router();

$r->addRoute('GET', '/filestore', new Index());
$r->addRoute('GET', '/filestore/files', new GetFilelist($_ENV['STATIC_URL'], $_ENV['ADMINISTRATOR_KEY']));
$r->addRoute('GET', '/filestore/files/{id:[0-9a-z\.]+}', new GetFile($_ENV['STATIC_URL'], $db));
$r->addRoute('POST', '/filestore', new UploadFile($_ENV['STATIC_URL']));

$app->addMiddleware($r);

$app->run(Request::createFromGlobals());
