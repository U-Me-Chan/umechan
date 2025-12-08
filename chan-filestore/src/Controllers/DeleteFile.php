<?php

namespace IH\Controllers;

use Rweb\IController;
use Symfony\Component\HttpFoundation\Request;
use IH\Http\Response;
use IH\DTO\Error as DTOError;
use IH\DTO\FileDeleted as DTOFileDeleted;
use IH\Services\Files;

class DeleteFile implements IController
{
    public function __construct(
        private string $maintenance_key,
        private Files $files
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

        $this->files->deleteFile($vars['id']);

        return new Response(new DTOFileDeleted());
    }
}
