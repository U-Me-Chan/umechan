<?php

namespace Ridouchire\RadioScheduler\Http\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(schema: 'QueueTrack')]
abstract class QueueTrack
{
    #[OA\Property]
    private string $file;

    #[OA\Property]
    private string $last_modified;

    #[OA\Property]
    private string $format;

    #[OA\Property]
    private string $artist;

    #[OA\Property]
    private string $albumartist;

    #[OA\Property]
    private string $title;

    #[OA\Property]
    private string $album;

    #[OA\Property]
    private int $track;

    #[OA\Property]
    private int $date;

    #[OA\Property]
    private string $genre;

    #[OA\Property]
    private int $disc;

    #[OA\Property]
    private string $label;

    #[OA\Property]
    private int $time;

    #[OA\Property]
    private float $duration;

    #[OA\Property]
    private int $pos;

    #[OA\Property]
    private int $id;

    private function __clone() {}
    private function __construct() {}
}
