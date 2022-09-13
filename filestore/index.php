<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

use Rweb\App;
use Rweb\Middlewares\Router;
use Symfony\Component\HttpFoundation\Request;
use IH\Controllers\Index;
use IH\Controllers\UploadFile;

require "vendor/autoload.php";

$app = App::create();

/** @var Router */
$r = new Router();

$r->addRoute('GET', '/', new Index());
$r->addRoute('POST', '/', new UploadFile());

$app->addMiddleware($r);

$app->run(Request::createFromGlobals());
