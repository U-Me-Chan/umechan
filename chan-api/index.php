<?php

use Medoo\Medoo;
use OpenApi\Generator;
use PK\RequestHandlers\Router;
use PK\Application;
use PK\Http\Request;

use PK\Feed\Controllers\BoardsFetcher;

use PK\Boards\BoardStorage;
use PK\Boards\Controllers\GetBoardList;

use PK\Events\Controllers\GetEventList;
use PK\Events\EventStorage;
use PK\Events\Services\EventTrigger;
use PK\OpenApi\Controllers\GetOpenApiSpecification;
use PK\OpenApi\Controllers\GetRedocPage;
use PK\Posts\PostStorage;
use PK\Posts\Controllers\GetThread;
use PK\Posts\Controllers\GetThreadList;
use PK\Posts\Controllers\CreateThread;
use PK\Posts\Controllers\CreateReply;
use PK\Posts\Controllers\UpdatePost;
use PK\Posts\Controllers\DeletePost;
use PK\Posts\Controllers\PostDeleter;

use PK\Passports\Controllers\CreatePassport;
use PK\Passports\Controllers\GetPassportList;
use PK\Passports\PassportStorage;
use PK\Posts\Services\PostFacade;
use PK\RequestHandlers\MemcachedRequestHandler;

require_once "vendor/autoload.php";

/** @var array */
$config = require "config.php";

define('BASE_URL', "https?\:\/\/" . preg_quote($_ENV['DOMAIN'], '/'));

/** @var string[] */
$exclude_tags = explode(',', $config['exclude_tags']);

/** @var string */
$maintenance_key = $config['maintenance_key'];

/** @var string */
$default_name = $config['default_name'];

/** @var Medoo */
$db = new Medoo([
    'database_type' => 'mysql',
    'database_name' => $config['db']['database'],
    'server'        => $config['db']['hostname'],
    'username'      => $config['db']['username'],
    'password'      => $config['db']['password'],
    'charset'       => 'utf8mb4',
    'collation'     => 'utf8mb4_unicode_ci'
]);

$event_storage    = new EventStorage($db);
$passport_storage = new PassportStorage($db);
$board_storage    = new BoardStorage($db);
$post_storage     = new PostStorage($db, $board_storage, $passport_storage);

$event_trigger = new EventTrigger($event_storage);

$post_facade = new PostFacade($post_storage, $board_storage, $event_trigger);

/** @var Router */
$r = new Router();

$r->addRoute('GET', '/board/all', new BoardsFetcher($board_storage, $db, $exclude_tags));

$r->addRoute('GET', '/v2/board', new GetBoardList($board_storage, $exclude_tags));
$r->addRoute('GET', '/v2/board/{tags:[a-z\+]+}', new GetThreadList($post_storage, $board_storage, $exclude_tags));

$r->addRoute('GET', '/v2/post/{id:[0-9]+}', new GetThread($post_storage, $board_storage, $exclude_tags));
$r->addRoute('POST', '/v2/post', new CreateThread($post_facade));
$r->addRoute('PUT', '/v2/post/{id:[0-9]+}', new CreateReply($post_facade));
$r->addRoute('PATCH', '/v2/post/{id:[0-9]+}', new UpdatePost($maintenance_key));
$r->addRoute('DELETE', '/v2/post/{id:[0-9]+}', new PostDeleter($post_facade));
$r->addRoute('DELETE', '/_/v2/post/{id:[0-9]+}', new DeletePost($post_facade, $maintenance_key));

$r->addRoute('GET', '/v2/passport', new GetPassportList($passport_storage));
$r->addRoute('POST', '/v2/passport', new CreatePassport($passport_storage, $default_name));

$r->addRoute('GET', '/v2/event', new GetEventList($event_storage));

$r->addRoute('GET', '/v2/_/openapi.json', new GetOpenApiSpecification(new Generator()));
$r->addRoute('GET', '/v2/_/redoc.html', new GetRedocPage($_ENV['DOMAIN']));

$app = new Application(
    new MemcachedRequestHandler(sucessor: $r),
    $config
);

$request = new Request($_SERVER, $_POST);

$app->run($request);
