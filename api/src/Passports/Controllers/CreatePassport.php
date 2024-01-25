<?php

namespace PK\Passports\Controllers;

use OpenApi\Attributes as OA;
use PK\Http\Request;
use PK\Http\Response;
use PK\Passports\Passport\Passport;
use PK\Passports\PassportStorage;

final class CreatePassport
{
    public function __construct(
        private PassportStorage $passport_repo,
        private string $default_name
    ) {
    }

    #[OA\Post(
        path: '/api/v2/passport',
        parameters: [
            new OA\Parameter(
                name: 'name',
                in: 'query',
                required: true,
                allowEmptyValue: false,
                schema: new OA\Schema(
                    type: 'string'
                )
            ),
            new OA\Parameter(
                name: 'key',
                in: 'query',
                required: true,
                allowEmptyValue: false,
                schema: new OA\Schema(
                    type: 'string'
                )
            )
        ]
    )]
    #[OA\Response(
        response: 201,
        description: 'Успешный ответ',
        content: new OA\JsonContent()
    )]
    #[OA\Response(
        response: 400,
        description: 'Если не переданы параметры запроса или они пусты'
    )]
    #[OA\Response(
        response: 409,
        description: 'Если в качестве значения для поля name или key использовано стандартное имя автора'
    )]
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
