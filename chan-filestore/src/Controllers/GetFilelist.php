<?php

namespace IH\Controllers;

use Symfony\Component\HttpFoundation\Request;
use Rweb\IController;
use IH\Http\Response;
use IH\DTO\FileList as DTOFileList;
use IH\Services\Files;
use IH\FileCollection;

class GetFilelist implements IController
{
    public function __construct(
        private Files $files
    ) {
    }

    public function __invoke(Request $req, array $vars = []): Response
    {
        /** @var FileCollection */
        $collection = $this->files->getFileList(
            $req->query->get('offset', 0),
            $req->query->get('limit', 20)
        );

        return new Response(new DTOFileList($collection->files, $collection->count));
    }
}
