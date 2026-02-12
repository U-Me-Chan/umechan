<?php

use Medoo\Medoo;
use Symfony\Component\Console\Application as ConsoleApplication;
use OpenApi\Generator;
use PK\RequestHandlers\Router;
use PK\RequestHandlers\MemcachedRequestHandler;
use PK\Application;
use PK\Base\Controllers\GetDebugRequestData;
use PK\Http\Request;

use PK\Feed\Controllers\BoardsFetcher;

use PK\Boards\BoardStorage;
use PK\Boards\Console\CreateBoard;
use PK\Boards\Controllers\GetBoardList;
use PK\Boards\Services\BoardService;
use PK\Events\ChanEventBuilder;
use PK\Events\FilestoreEventBuilder;
use PK\Events\MessageBrokers\KafkaWrapper;
use PK\OpenApi\Controllers\GetOpenApiSpecification;
use PK\OpenApi\Controllers\GetRedocPage;
use PK\Posts\PostStorage;
use PK\Posts\Controllers\GetThread;
use PK\Posts\Controllers\GetThreadList;
use PK\Posts\Controllers\CreateThread;
use PK\Posts\Controllers\CreateReply;

use PK\Passports\Controllers\CreatePassport;
use PK\Passports\Controllers\GetPassportList;
use PK\Passports\PassportStorage;
use PK\Passports\Services\PassportService;
use PK\Posts\Console\RestorePostsFromEPDSDump;
use PK\Posts\Console\SetBlockedThread;
use PK\Posts\Console\SetStickyThread;
use PK\Posts\Console\UnsetBlockedThread;
use PK\Posts\Console\UnsetStickyThread;
use PK\Posts\Controllers\DeletePostByAuthor;
use PK\Posts\Controllers\DeletePostByOwnerChan;
use PK\Posts\Controllers\GetThreadFileList;
use PK\Posts\Controllers\UpdatePost;
use PK\Posts\Services\PostService;
use PK\Posts\Services\PostRestorator;
use PK\Services\HookService;

require_once __DIR__ . "/vendor/autoload.php";

/** @var array */
$config = require_once __DIR__ . '/config.php';

define('BASE_URL', "https?\:\/\/" . preg_quote($_ENV['DOMAIN'], '/'));

/** @var string[] */
$exclude_tags = explode(',', $config['exclude_tags']);

/** @var string */
$maintenance_key = $config['maintenance_key'];

/** @var string */
$default_name = $config['default_name'];

$hook_service = new HookService();

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

$message_broker = new KafkaWrapper(['kafka:9092']);

$chan_event_builder = new ChanEventBuilder($config['node_sign']);
$filestore_event_builder = new FilestoreEventBuilder('foo');

$passport_storage = new PassportStorage($db);
$board_storage    = new BoardStorage($db);
$post_storage     = new PostStorage($db);

$board_service    = new BoardService($board_storage, $message_broker, $chan_event_builder);
$passport_service = new PassportService($passport_storage, $message_broker, $chan_event_builder, $default_name);
$post_restorator  = new PostRestorator('/tmp/dumps/' . $_ENV['EPDS_DUMP_PATH'], $board_storage, $post_storage);
$post_service     = new PostService(
    $post_storage,
    $board_service,
    $passport_service,
    $message_broker,
    $chan_event_builder,
    $filestore_event_builder,
    $post_restorator,
    $hook_service
);

if (PHP_SAPI == 'cli') {
    $app = new ConsoleApplication('ChanApi');

    $app->add(new RestorePostsFromEPDSDump($post_service));
    $app->add(new SetStickyThread($post_service));
    $app->add(new UnsetStickyThread($post_service));
    $app->add(new CreateBoard($board_service));
    $app->add(new SetBlockedThread($post_service));
    $app->add(new UnsetBlockedThread($post_service));

    exit($app->run());
}

/** @var Router */
$r = new Router();

$r->addRoute('GET', '/test', new GetDebugRequestData());
$r->addRoute('POST', '/test', new GetDebugRequestData());
$r->addRoute('PUT', '/test', new GetDebugRequestData());
$r->addRoute('PATCH', '/test', new GetDebugRequestData());
$r->addRoute('DELETE', '/test', new GetDebugRequestData());

$r->addRoute('GET', '/board/all', new BoardsFetcher($board_service, $db, $exclude_tags));

$r->addRoute('GET', '/v2/board', new GetBoardList($board_service, $exclude_tags));
$r->addRoute('GET', '/v2/board/{tags:[a-z\+]+}', new GetThreadList($post_service, $exclude_tags));

$r->addRoute('GET', '/v2/post/{id:[0-9]+}', new GetThread($post_service, $exclude_tags));
$r->addRoute('POST', '/v2/post', new CreateThread($post_service));
$r->addRoute('PUT', '/v2/post/{id:[0-9]+}', new CreateReply($post_service));
$r->addRoute('PATCH', '/v2/post/{id:[0-9]+}', new UpdatePost($post_service));
$r->addRoute('DELETE', '/v2/post/{id:[0-9]+}', new DeletePostByAuthor($post_service));
$r->addRoute('POST', '/_/v2/post/{id:[0-9]+}', new DeletePostByOwnerChan($post_service, $maintenance_key));
$r->addRoute('GET', '/v2/post/{id:[0-9]+}/files', new GetThreadFileList($post_service));

$r->addRoute('GET', '/v2/passport', new GetPassportList($passport_storage));
$r->addRoute('POST', '/v2/passport', new CreatePassport($passport_service));

$r->addRoute('GET', '/v2/_/openapi.json', new GetOpenApiSpecification(new Generator()));
$r->addRoute('GET', '/v2/_/redoc.html', new GetRedocPage($_ENV['DOMAIN']));

$request_sucessor = $_ENV['IS_DEV'] == 'yes' ? $r : new MemcachedRequestHandler(sucessor: $r);

$request = new Request($_SERVER, $_POST);

$app = new Application($r, $hook_service);

$app->run($request);
