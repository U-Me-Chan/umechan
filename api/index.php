<?php

use Medoo\Medoo;
use PK\Router;
use PK\Application;
use PK\Http\Request;
use PK\Database\BoardRepository;
use PK\Database\PostRepository;
use PK\Controllers\BoardsFetcher;
use PK\Controllers\PostFetcher;
use PK\Controllers\PostCreator;
use PK\Controllers\PostDeleter;
use PK\Controllers\PostBoardFetcher;

use PK\Boards\BoardStorage;
use PK\Posts\PostStorage;
use PK\Boards\Controllers\GetBoardList;
use PK\Posts\Controllers\GetThread;
use PK\Posts\Controllers\GetThreadList;
use PK\Posts\Controllers\CreateThread;
use PK\Posts\Controllers\CreateReply;
use PK\Posts\Controllers\UpdatePost;
use PK\Posts\Controllers\DeletePost;
use PK\Passports\Controllers\CreatePassport;
use PK\Passports\Controllers\GetPassportList;
use PK\Passports\PassportStorage;
use PK\Tracks\Controllers\GetTrackList;
use PK\Tracks\TrackRepository;

require_once "vendor/autoload.php";

/** @var array */
$config = require "config.php";

/** @var array|Application */
$app = new Application($config);

$app['request'] = new Request($_SERVER, $_POST, $_FILES);
$app['router'] = new Router();

$app['db'] = function ($app) {
    return new Medoo([
        'database_type' => 'mysql',
        'database_name' => $app['config']['db']['database'],
        'server' => $app['config']['db']['hostname'],
        'username' => $app['config']['db']['username'],
        'password' => $app['config']['db']['password'],
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci'
    ]);
};

$board_repo = new BoardRepository($app['db']);
$post_repo  = new PostRepository($app['db']);

$board_storage = new BoardStorage($app['db']);
$post_storage = new PostStorage($app['db'], $board_storage);

$passport_storage = new PassportStorage($app['db']);

$tracks_repo = new TrackRepository($app['db']);

/** @var Router */
$r = $app['router'];

$r->addRoute('GET', '/board/all', new BoardsFetcher($board_storage, $app['db']));
$r->addRoute('GET', '/board/{tag}', new PostBoardFetcher($board_storage, $post_storage));
$r->addRoute('GET', '/post/{id:[0-9]+}', new PostFetcher($post_repo));
$r->addRoute('POST', '/post', new PostCreator($post_repo, $board_repo));
$r->addRoute('DELETE', '/post/{id:[0-9]+}', new PostDeleter($post_repo));

$r->addRoute('GET', '/v2/board', new GetBoardList($board_storage));
$r->addRoute('GET', '/v2/board/{tags:[a-z\+]+}', new GetThreadList($post_storage));

$r->addRoute('GET', '/v2/post/{id:[0-9]+}', new GetThread($post_storage));
$r->addRoute('POST', '/v2/post', new CreateThread($board_storage, $post_storage));
$r->addRoute('PUT', '/v2/post/{id:[0-9]+}', new CreateReply($post_storage));
$r->addRoute('PATCH', 'v2/post/{id:[0-9]+}', new UpdatePost($post_storage, $config['maintenance_key']));
$r->addRoute('DELETE', '/v2/post/{id:[0-9]+}', new DeletePost($post_storage, $config['maintenance_key']));

$r->addRoute('GET', '/v2/passport', new GetPassportList($passport_storage));
$r->addRoute('POST', '/v2/passport', new CreatePassport($passport_storage, $app['config']['default_name']));

$r->addRoute('GET', '/radio/tracks', new GetTrackList($tracks_repo));

$app->run();
