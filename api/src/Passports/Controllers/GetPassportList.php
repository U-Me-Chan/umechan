<?php

namespace PK\Passports\Controllers;

use PK\Http\Request;
use PK\Http\Response;
use Pk\Passports\PassportStorage;
use OpenApi\Attributes as OA;

final class GetPassportList
{
    public function __construct(
        private PassportStorage $passport_repo
    ) {
    }

    #[OA\Get(path: '/api/v2/passport', description: 'Возвращает список зарегистрированных имён')]
    #[OA\Response(
        response: 200,
        description: 'Ответ содержит список имён и их количество',
        content: new OA\JsonContent(
            title: 'Схема ответа',
            properties: [
                new OA\Property(
                    property: 'payload',
                    type: 'object',
                    properties: [
                        new OA\Property(
                            property: 'count',
                            type: 'integer',
                            title: 'Количество имён'
                        ),
                        new OA\Property(
                            property: 'passports',
                            type: 'array',
                            items: new OA\Items(ref: '#/components/schemas/Passport')
                        )
                    ]
                ),
                new OA\Property(
                    property: 'version',
                    type: 'string'
                ),
                new OA\Property(
                    property: 'error',
                    type: null
                )
            ]
        )
    )]
    public function __invoke(Request $req): Response
    {
        list($passports, $count) = $this->passport_repo->fetch();

        return new Response([
            'passports' => $passports,
            'count'     => $count
        ], 200);
    }
}
