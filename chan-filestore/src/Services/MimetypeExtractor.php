<?php

namespace IH\Services;

use IH\Enums\Mimetype;

interface MimetypeExtractor
{
    public function extract(string $path_to_file): Mimetype;
}
