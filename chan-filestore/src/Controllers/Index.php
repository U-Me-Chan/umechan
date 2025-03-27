<?php

namespace IH\Controllers;

use Rweb\IController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class Index implements IController
{
    public function __invoke(Request $req, array $vars = []): Response
    {
        return new Response('coming soon', Response::HTTP_OK);
    }
}
