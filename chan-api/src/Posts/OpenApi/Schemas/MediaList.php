<?php

namespace PK\Posts\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema]
abstract class MediaList
{
    /**
     * @var list<array{link: string, preview: string, type: 'video'}> $videos
     */
    #[OA\Property(items: new OA\Items(ref: Media::class))]
    public array $videos;

    /**
     * @var list<array{link: string, preview: string, type: 'image'}> $images
     */
    #[OA\Property(items: new OA\Items(ref: Media::class))]
    public array $images;

    /**
     * @var list<array{link: string, preview: string}> $youtubes
     */
    #[OA\Property(items: new OA\Items(ref: Media::class))]
    public array $youtubes;
}
