<?php

use Evenement\EventEmitter;
use Medoo\Medoo;

use OpenApi\Attributes as OA;

use PK\Router;
use PK\Application;
use PK\Http\Request;

use PK\Boards\Controllers\GetBoardList;
use PK\Boards\Repositories\MedooBoardRepository;
##FIXME: перекатить владельца писсичана на список тредов с реплаями либо перекатить эту модель на вторую версию API
use PK\Controllers\BoardsFetcher;
##FIXME

use PK\Events\Event\EventType;
use PK\Events\Repositories\MedooEventRepository;
use PK\Events\Controllers\GetEventList;
use PK\Events\EventCallbacks\BoardUpdatedHandler;
use PK\Events\EventCallbacks\PostCreatedHandler;
use PK\Events\EventCallbacks\PostDeletedHandler;
use PK\Events\EventCallbacks\ThreadUpdatedHandler;

use PK\Posts\Repositories\MedooPostByBoardRepository;
use PK\Posts\Controllers\GetThread;
use PK\Posts\Controllers\GetThreadList;
use PK\Posts\Controllers\CreateThread;
use PK\Posts\Controllers\CreateReply;
use PK\Posts\Controllers\DeletePost;

use PK\Passports\Repositories\MedooPassportRepository;
use PK\Passports\Controllers\CreatePassport;
use PK\Passports\Controllers\GetPassportList;

use PK\Tracks\TrackRepository;
use PK\Tracks\Controllers\GetTrackList;

require_once "vendor/autoload.php";

/** @var array */
$config = require "config.php";

/** @var array|Application */
$app = new Application($config);

$req = $app['request'] = new Request($_SERVER, $_POST, $_FILES);

if ($req->getParams('timezone')) {
    date_default_timezone_set($req->getParams('timezone'));
}

$app['router'] = new Router();

$app['db'] = function ($app) {
    return new Medoo([
        'database_type' => 'mysql',
        'database_name' => $app['config']['db']['database'],
        'server'        => $app['config']['db']['hostname'],
        'username'      => $app['config']['db']['username'],
        'password'      => $app['config']['db']['password'],
        'charset'       => 'utf8mb4',
        'collation'     => 'utf8mb4_unicode_ci'
    ]);
};

$board_repo         = new MedooBoardRepository($app['db']);
$post_by_board_repo = new MedooPostByBoardRepository($app['db']);
$passport_repo      = new MedooPassportRepository($app['db']);
$event_repo         = new MedooEventRepository($app['db']);

$events = $app['events'] = new EventEmitter();

$events->on(EventType::PostCreated->value, new PostCreatedHandler($event_repo));
$events->on(EventType::PostDeleted->value, new PostDeletedHandler($event_repo));
$events->on(EventType::BoardUpdateTriggered->value, new BoardUpdatedHandler($event_repo, $board_repo, $post_by_board_repo));
$events->on(EventType::ThreadUpdateTriggered->value, new ThreadUpdatedHandler($event_repo, $post_by_board_repo));

/** @var Router */
$r = $app['router'];

$r->addRoute('GET', '/board/all', new BoardsFetcher($board_repo, $app['db']));

$r->addRoute('GET', '/v2/board', new GetBoardList($board_repo));
$r->addRoute('GET', '/v2/board/{tags:[a-z\+]+}', new GetThreadList($post_by_board_repo));
$r->addRoute('GET', '/v2/post/{id:[0-9]+}', new GetThread($post_by_board_repo));

$r->addRoute('POST', '/v2/post', new CreateThread($board_repo, $post_by_board_repo, $passport_repo, $events));
$r->addRoute('PUT', '/v2/post/{id:[0-9]+}', new CreateReply($post_by_board_repo, $passport_repo, $events));
$r->addRoute('DELETE', '/v2/post/{id:[0-9]+}', new DeletePost($post_by_board_repo, $events));

$r->addRoute('GET', '/v2/passport', new GetPassportList($passport_repo));
$r->addRoute('POST', '/v2/passport', new CreatePassport($passport_repo, $app['config']['default_name']));

$r->addRoute('GET', '/v2/events', new GetEventList($event_repo));

$tracks_repo = new TrackRepository($app['db']);

$r->addRoute('GET', '/radio/tracks', new GetTrackList($tracks_repo));

$app->run();
