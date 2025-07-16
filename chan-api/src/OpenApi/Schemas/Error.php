<?php

namespace PK\OpenApi\Schemas;

use Attribute;
use OpenApi\Attributes as OA;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class Error extends OA\Response
{
    public function __construct(
        string|int $response,
        string $description,
            ?string $type = null,
        ?string $message = null
    ) {
        parent::__construct(
            response: $response,
            description: $description,
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(
                            property: 'payload',
                            description: 'Полезная нагрузка',
                            type: 'array',
                            default: [],
                            items: new OA\Items()
                        ),
                        new OA\Property(
                            property: 'error',
                            description: 'Ошибка выполнения запроса',
                            type: 'object',
                            properties: [
                                new OA\Property(
                                    property: 'type',
                                    type: 'string',
                                    default: $type
                                ),
                                new OA\Property(
                                    property: 'message',
                                    type: 'string',
                                    default: $message
                                )
                            ]
                        )
                    ]
                )
            )
        );
    }
}
