<?php

namespace Ridouchire\RadioScheduler\Http\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema]
abstract class Track
{
    #[OA\Property]
    private int $id;

    #[OA\Property]
    private int $first_playing;

    #[OA\Property]
    private int $last_playing;

    #[OA\Property]
    private int $play_count;

    #[OA\Property]
    private int $estimate;

    #[OA\Property]
    private int $duration;

    #[OA\Property]
    private string $path;

    #[OA\Property(deprecated: true)]
    private null $mpd_track_id;

    #[OA\Property]
    private string $artist;

    #[OA\Property]
    private string $title;

    #[OA\Property]
    private string $hash;

    private function __construct() {}
    private function __clone() {}
}
