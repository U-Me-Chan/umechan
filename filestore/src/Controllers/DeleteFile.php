<?php

namespace IH\Controllers;

use Rweb\IController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DeleteFile implements IController
{
    public function __construct(
        private string $maintenance_key
    ) {
    }

    public function __invoke(Request $req, array $vars = []): Response
    {
        if (!$req->headers->get('key')) {
            return new Response('', Response::HTTP_UNAUTHORIZED, $this->getHeaders());
        }

        if ($req->headers->get('key') !== $this->maintenance_key) {
            return new Response('', Response::HTTP_UNAUTHORIZED, $this->getHeaders());
        }

        $filepath      = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'files' . DIRECTORY_SEPARATOR . "{$vars['id']}";
        $thumbnailpath = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'files' . DIRECTORY_SEPARATOR . "thumb.{$vars['id']}";

        if (is_file($filepath)) {
            unlink($filepath);
        }

        if (is_file($thumbnailpath)) {
            unlink($thumbnailpath);
        }

        return new Response('', Response::HTTP_OK, $this->getHeaders());
    }

    private function getHeaders(): array
    {
        return [
            'Content-type'                 => 'application/json',
            'Access-Control-Allow-Origin'  => '*',
            'Access-Control-Allow-Methods' => '*',
            'Access-Control-Allow-Headers' => '*'
        ];
    }
}
