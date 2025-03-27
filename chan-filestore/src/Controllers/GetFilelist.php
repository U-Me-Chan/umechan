<?php

namespace IH\Controllers;

use Rweb\IController;
use Symfony\Component\HttpFoundation\Request;
use IH\Http\Response;
use IH\DTO\FileList as DTOFileList;

class GetFilelist implements IController
{
    public function __construct(
        private string $static_url
    ) {
    }

    public function __invoke(Request $req, array $vars = []): Response
    {
        $limit  = $req->query->get('limit', 20);
        $offset = $req->query->get('offset', 0);

	$files = glob(
            __DIR__ . DIRECTORY_SEPARATOR .
                '..' .DIRECTORY_SEPARATOR .
                '..' . DIRECTORY_SEPARATOR .
                'files' . DIRECTORY_SEPARATOR .
                '[!{thumb}]*',
            GLOB_BRACE
        );

        $files = array_map(function (string $path) {
            if (substr($path, -3) == 'mp4' || substr($path, -4) == 'webm') {
                $data['thumbnail'] = $this->static_url . '/thumb.' . substr($path, 42) . '.' . 'jpeg';
            } else {
                $data['thumbnail'] = $this->static_url . '/thumb.' . substr($path, 42);
            }

            $data['original']  = $this->static_url . '/' . substr($path, 42);
            $data['name']      = substr($path, 42);

            return $data;
        }, $files);

        $count = sizeof($files);

        $files = array_slice($files, $offset, $limit);

        return new Response(new DTOFileList($files, $count));
    }
}
