<?php

namespace PK\Posts\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema]
abstract class Media
{
    #[OA\Property(description: 'Ссылка на оригинальный файл')]
    public string $link;
    #[OA\Property(description: 'Превью файла')]
    public string $preview;
}
