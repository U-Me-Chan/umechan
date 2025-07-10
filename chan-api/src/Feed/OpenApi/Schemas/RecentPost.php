<?php

namespace PK\Feed\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema]
abstract class RecentPost
{
    #[OA\Property]
    public int $id;
    #[OA\Property]
    public string $poster;
    #[OA\Property]
    public string $subject;
    #[OA\Property]
    public string $message;
    #[OA\Property]
    public int $timestamp;
    #[OA\Property]
    public ?int $parent_id;
    #[OA\Property]
    public string $is_verify;
    #[OA\Property]
    public string $tag;
}
