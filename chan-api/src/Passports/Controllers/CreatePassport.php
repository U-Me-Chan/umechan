<?php

namespace PK\Passports\Controllers;

use OpenApi\Attributes as OA;
use PK\Http\Request;
use PK\Http\Responses\JsonResponse;
use PK\OpenApi\Schemas\Error;
use PK\OpenApi\Schemas\Response;
use PK\Passports\Exceptions\NameOrKeyIsForbiddenException;
use PK\Passports\Services\PassportService;

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
    message: 'Нельзя использовать такое имя или пароль'
)]
final class CreatePassport
{
    public function __construct(
        private PassportService $passport_service
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

        try {
            $this->passport_service->createPassport($req->getParams('name'), $req->getParams('key'));
        } catch (NameOrKeyIsForbiddenException $e) {
            return new JsonResponse([], 409)->setException($e);
        }

        return new JsonResponse([], 201);
    }
}
