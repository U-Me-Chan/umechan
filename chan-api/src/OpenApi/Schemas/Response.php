<?php

namespace PK\OpenApi\Schemas;

use Attribute;
use OpenApi\Attributes as OA;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class Response extends OA\Response
{
    public function __construct(
        string|int $response,
        string $description,
        ?string $payload_reference = null,
        ?string $error_reference = null
    ) {
        parent::__construct(
            response: $response,
            description: $description,
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    properties: [
                        $payload_reference ? new OA\Property(
                            property: 'payload',
                            description: 'Полезная нагрузка',
                            ref: $payload_reference
                        ) : new OA\Property(
                            property: 'payload',
                            type: 'array',
                            default: [],
                            items: new OA\Items()
                        ),
                        $error_reference ? new OA\Property(
                            property: 'error',
                            description: 'Ошибка выполнения запроса',
                            ref: $error_reference
                        ) : new OA\Property(
                            property: 'error',
                            type: 'null'
                        )
                    ]
                )
            )
        );
    }
}
