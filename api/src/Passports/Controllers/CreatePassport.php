<?php

namespace PK\Passports\Controllers;

use PK\Http\Request;
use PK\Http\Response;
use PK\Passports\IPassportRepository;
use PK\Passports\Passport\Passport;

final class CreatePassport
{
    public function __construct(
        private IPassportRepository $passport_repo,
        private string $default_name
    ) {
    }

    public function __invoke(Request $req): Response
    {
        if ($req->getParams('name') == null) {
            return (new Response([], 400))
                ->setException(new \InvalidArgumentException("Параметр name не передан"));
        }

        if (empty($req->getParams('name'))) {
            return (new Response([], 400))
                ->setException(new \InvalidArgumentException("Параметр name не может быть пустым"));
        }

        if ($req->getParams('key') == null) {
            return (new Response([], 400))
                ->setException(new \InvalidArgumentException("Параметр key не передан"));
        }

        if (empty($req->getParams('key'))) {
            return (new Response([], 400))
                ->setException(new \InvalidArgumentException("Параметр key не может быть пустым"));
        }

        if ($req->getParams('name') == $this->default_name || $req->getParams('key') == $this->default_name) {
            return (new Response([], 409))
                ->setException(
                    new \InvalidArgumentException("Нельзя использовать имя автора по умолчанию для любого из параметров: {$this->default_name}")
                );
        }

        $passport = Passport::draft($req->getParams('name'), $req->getParams('key'));

        $this->passport_repo->save($passport);

        return new Response([], 201);
    }
}
