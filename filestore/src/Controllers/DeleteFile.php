<?php

namespace IH\Controllers;

use Rweb\IController;
use Symfony\Component\HttpFoundation\Request;
use IH\Http\Response;
use IH\DTO\Error as DTOError;
use IH\DTO\FileDeleted as DTOFileDeleted;

class DeleteFile implements IController
{
    public function __construct(
        private string $maintenance_key
    ) {
    }

    public function __invoke(Request $req, array $vars = []): Response
    {
        if (!$req->headers->get('key')) {
            return new Response(new DTOError('unauthorized'), Response::HTTP_UNAUTHORIZED);
        }

        if ($req->headers->get('key') !== $this->maintenance_key) {
            return new Response(new DTOError('unauthorized'), Response::HTTP_UNAUTHORIZED);
        }

        $filepath      = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'files' . DIRECTORY_SEPARATOR . "{$vars['id']}";
        $thumbnailpath = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'files' . DIRECTORY_SEPARATOR . "thumb.{$vars['id']}";

        if (is_file($filepath)) {
            unlink($filepath);
        }

        if (is_file($thumbnailpath)) {
            unlink($thumbnailpath);
        }

        return new Response(new DTOFileDeleted());
    }
}
