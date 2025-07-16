<?php

namespace PK\Passports\Controllers;

use OpenApi\Attributes as OA;
use PK\Http\Responses\JsonResponse;
use PK\OpenApi\Schemas\Response;
use PK\Passports\OpenApi\Schemas\PassportList;
use PK\Passports\PassportStorage;

#[OA\Get(
    path: '/api/v2/passport',
    operationId: 'getPassportList',
    summary: 'Получить список зарегистрированных имён',
    tags: ['passport']
)]
#[Response(
    response: 200,
    description: 'Список имён',
    payload_reference: PassportList::class
)]
final class GetPassportList
{
    public function __construct(
        private PassportStorage $passport_repo
    ) {
    }

    public function __invoke(): JsonResponse
    {
        list($passports, $count) = $this->passport_repo->fetch();

        return new JsonResponse([
            'passports' => $passports,
            'count'     => $count
        ], 200);
    }
}
