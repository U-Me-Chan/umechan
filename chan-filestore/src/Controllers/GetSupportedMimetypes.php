<?php

namespace IH\Controllers;

use Symfony\Component\HttpFoundation\Request;
use Rweb\IController;
use IH\DTO\SupportedMimetypes;
use IH\Enums\Mimetype;
use IH\Http\Response;

class GetSupportedMimetypes implements IController
{
    public function __invoke(Request $req, array $vars = []): Response
    {
        return new Response(new SupportedMimetypes(Mimetype::getAll()));
    }
}
