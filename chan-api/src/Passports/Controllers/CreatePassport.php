<?php

namespace PK\Passports\Controllers;

use OpenApi\Attributes as OA;
use PK\Http\Request;
use PK\Http\Responses\JsonResponse;
use PK\OpenApi\Schemas\Error;
use PK\OpenApi\Schemas\Response;
use PK\Passports\Passport;
use PK\Passports\PassportStorage;

#[OA\Post(
    path: '/api/v2/passport',
    operationId: 'createPassport',
    summary: 'Создать новое имя',
    tags: ['passport'],
    requestBody: new OA\RequestBody(
        content: new OA\MediaType(
            mediaType: 'application/json',
            schema: new OA\Schema(
                properties: [
                    new OA\Property(
                        property: 'name',
                        type: 'string',
                    ),
                    new OA\Property(
                        property: 'key',
                        type: 'string'
                    )
                ]
            )
        )
    )
)]
#[Response(
    response: 200,
    description: 'Имя создано и связано с ключом'
)]
#[Error(
    response: 400,
    description: 'Ошибка при разборе запроса',
    type: 'InvalidArgumentException',
    message: 'Параметр name не передан'
)]
#[Error(
    response: 409,
    description: 'При попытке использовать недопустимые значения',
    type: 'InvalidArgumentException',
    message: 'Нельзя использовать имя автора по умолчанию для любого из параметров: Anon'
)]
final class CreatePassport
{
    public function __construct(
        private PassportStorage $passport_repo,
        private string $default_name
    ) {
    }

    public function __invoke(Request $req): JsonResponse
    {
        if ($req->getParams('name') == null) {
            return (new JsonResponse([], 400))
                ->setException(new \InvalidArgumentException("Параметр name не передан"));
        }

        if (empty($req->getParams('name'))) {
            return (new JsonResponse([], 400))
                ->setException(new \InvalidArgumentException("Параметр name не может быть пустым"));
        }

        if ($req->getParams('key') == null) {
            return (new JsonResponse([], 400))
                ->setException(new \InvalidArgumentException("Параметр key не передан"));
        }

        if (empty($req->getParams('key'))) {
            return (new JsonResponse([], 400))
                ->setException(new \InvalidArgumentException("Параметр key не может быть пустым"));
        }

        if ($req->getParams('name') == $this->default_name || $req->getParams('key') == $this->default_name) {
            return (new JsonResponse([], 409))
                ->setException(
                    new \InvalidArgumentException("Нельзя использовать имя автора по умолчанию для любого из параметров: {$this->default_name}")
                );
        }

        $passport = Passport::draft($req->getParams('name'), $req->getParams('key'));

        $this->passport_repo->save($passport);

        return new JsonResponse([], 201);
    }
}
