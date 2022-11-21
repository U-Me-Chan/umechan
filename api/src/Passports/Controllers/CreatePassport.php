<?php

namespace PK\Passports\Controllers;

use Medoo\Medoo;
use PK\Http\Request;
use PK\Http\Response;

final class CreatePassport
{
    public function __construct(
        private Medoo $db
    ) {
    }

    public function __invoke(Request $req): Response
    {
        if ($req->getParams('name') == null) {
            return (new Response([], 400))->setException(new \InvalidArgumentException("Параметр name не передан"));
        }

        if (empty($req->getParams('name'))) {
            return (new Response([], 400))->setException(new \InvalidArgumentException("Параметр name не может быть пустым"));
        }

        if ($req->getParams('key') == null) {
            return (new Response([], 400))->setException(new \InvalidArgumentException("Параметр key не передан"));
        }

        if (empty($req->getParams('key'))) {
            return (new Response([], 400))->setException(new \InvalidArgumentException("Параметр key не может быть пустым"));
        }

        try {
            $this->db->insert('passports', [
                'name' => $req->getParams('name'),
                'hash'  => hash('sha512', $req->getParams('key'))
            ]);
        } catch (\PDOException) {
            return (new Response([], 409))->setException(new \InvalidArgumentException("Такой ключ или имя уже используется, выберите иное"));
        }

        return new Response([], 201);
    }
}
