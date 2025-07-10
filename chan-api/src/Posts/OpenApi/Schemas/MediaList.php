<?php

namespace PK\Posts\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema]
abstract class MediaList
{
    #[OA\Property(items: new OA\Items(ref: Media::class))]
    public array $videos;
    #[OA\Property(items: new OA\Items(ref: Media::class))]
    public array $images;
    #[OA\Property(items: new OA\Items(ref: Media::class))]
    public array $youtubes;
}
