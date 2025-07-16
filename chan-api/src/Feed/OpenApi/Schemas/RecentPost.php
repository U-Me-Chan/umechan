<?php

namespace PK\Feed\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema]
abstract class RecentPost
{
    #[OA\Property(description: 'Идентификатор')]
    public int $id;
    #[OA\Property(description: 'Автор')]
    public string $poster;
    #[OA\Property(description: 'Тема')]
    public string $subject;
    #[OA\Property(description: 'Сообщение')]
    public string $message;
    #[OA\Property(description: 'Метка времени')]
    public int $timestamp;
    #[OA\Property]
    public ?int $parent_id;
    #[OA\Property(description: 'Верифицирован ли автор?')]
    public string $is_verify;
    #[OA\Property(description: 'Тег доски')]
    public string $tag;
}
