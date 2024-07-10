<?php

use Medoo\Medoo;
use PK\Router;
use PK\Http\Request;
use PK\Http\Response;

use PK\Boards\BoardStorage;
use PK\Boards\Controllers\GetBoardList;

use PK\Posts\PostStorage;
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

use PK\Events\Controllers\GetEventList;
use PK\Events\EventStorage;

require_once "vendor/autoload.php";

/** @var array */
$config = require "config.php";

$req = new Request($_SERVER, $_POST, $_FILES);
$r = new Router();

$db = new Medoo([
    'database_type' => 'mysql',
    'database_name' => $config['db']['database'],
    'server'        => $config['db']['hostname'],
    'username'      => $config['db']['username'],
    'password'      => $config['db']['password'],
    'charset'       => 'utf8mb4',
    'collation'     => 'utf8mb4_unicode_ci'
]);

$board_storage    = new BoardStorage($db);
$post_storage     = new PostStorage($db, $board_storage);
$passport_storage = new PassportStorage($db);
$tracks_repo      = new TrackRepository($db);
$event_storage    = new EventStorage($db);

$r->addRoute('GET', '/v2/board', new GetBoardList($board_storage));
$r->addRoute('GET', '/v2/board/{tags:[a-z\+]+}', new GetThreadList($post_storage));

$r->addRoute('GET', '/v2/post/{id:[0-9]+}', new GetThread($post_storage));
$r->addRoute('POST', '/v2/post', new CreateThread($board_storage, $post_storage, $event_storage));
$r->addRoute('PUT', '/v2/post/{id:[0-9]+}', new CreateReply($post_storage, $event_storage));
$r->addRoute('PATCH', '/v2/post/{id:[0-9]+}', new UpdatePost($post_storage, $config['maintenance_key']));
$r->addRoute('DELETE', '/v2/post/{id:[0-9]+}', new DeletePost($post_storage, $event_storage, $config['maintenance_key']));

$r->addRoute('GET', '/v2/passport', new GetPassportList($passport_storage));
$r->addRoute('POST', '/v2/passport', new CreatePassport($passport_storage, $config['default_name']));

$r->addRoute('GET', '/v2/events', new GetEventList($event_storage));

$r->addRoute('GET', '/radio/tracks', new GetTrackList($tracks_repo));

/** @var Response */
$res = $r->handle($req);

if (!empty($res->getHeaders())) {
    foreach ($this['request']->getHeaders() as $header) {
        header($header);
    }
}

header('Content-type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: *');
header('Access-Control-Allow-Headers: *');

http_response_code($res->getCode());
echo json_encode($res->getBody());

exit(0);
