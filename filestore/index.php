<?php

use Rweb\App;
use Rweb\Middlewares\Router;
use Symfony\Component\HttpFoundation\Request;
use IH\Controllers\Index;
use IH\Controllers\UploadFile;
use IH\Controllers\GetFilelist;

require "vendor/autoload.php";

$app = App::create();

/** @var Router */
$r = new Router();

$r->addRoute('GET', '/filestore', new Index());
$r->addRoute('GET', '/filestore/files', new GetFilelist($_ENV['STATIC_URL'], $_ENV['ADMINISTRATOR_KEY']));
$r->addRoute('POST', '/filestore', new UploadFile($_ENV['STATIC_URL']));

$app->addMiddleware($r);

$app->run(Request::createFromGlobals());
