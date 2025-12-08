<?php

use Medoo\Medoo;
use Rweb\App;
use Rweb\Middlewares\Router;
use Symfony\Component\HttpFoundation\Request;
use IH\Controllers\UploadFile;
use IH\Controllers\GetFilelist;
use IH\Controllers\DeleteFile;
use IH\FileRepository;
use IH\Services\Files;
use IH\Services\MimetypeExtractors\FinfoMimetypeExtractor;
use IH\Services\ThumbnailCreator;
use IH\Services\Thumbnailers\ImageThumbnailer;
use IH\Services\Thumbnailers\VideoThumbnailer;
use IH\Utils\DirectoryIterator;

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

$files_path = __DIR__ . DIRECTORY_SEPARATOR . 'files' . DIRECTORY_SEPARATOR;

$thumbnail_creator = new ThumbnailCreator();
$thumbnail_creator->register(new ImageThumbnailer($files_path));
$thumbnail_creator->register(new VideoThumbnailer($files_path));

$finfo_mimetype_extractor = new FinfoMimetypeExtractor();
$directory_iterator       = new DirectoryIterator($files_path . '[!{thumb,deleted}]*');

$file_repo = new FileRepository($db, $directory_iterator, $_ENV['STATIC_URL']);

$files_service = new Files(
    $finfo_mimetype_extractor,
    $thumbnail_creator,
    $file_repo,
    $_ENV['STATIC_URL'],
    $files_path
);

/** @var Router */
$r = new Router();

$r->addRoute('GET', '/filestore/files', new GetFilelist($files_service));
$r->addRoute('POST', '/filestore', new UploadFile($files_service));
$r->addRoute('DELETE', '/filestore/files/{id:[0-9a-z\.]+}', new DeleteFile($_ENV['ADMINISTRATOR_KEY'], $files_service));

$app->addMiddleware($r);

$app->run(Request::createFromGlobals());
